<script lang="ts">
	import {
		Button,
		ButtonGroup,
		FormControl,
		Modal,
		SplitControl,
		TextInput,
		Validation,
		toast,
		confirm,
		Radio,
		Tooltip,
		Textarea,
		Tag
	} from '@hyvor/design/components';
	import {
		createCustomDomainSetup,
		updateCustomDomain,
		deleteCustomDomainIntent,
		verifyCustomDomainSetup
	} from './hostingActions';
	import { hostingInfoStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { slide } from 'svelte/transition';
	import DnsInstructions from './DnsInstructions.svelte';
	import type {
		CustomDomainIntent,
		CustomDomainSetup,
		CustomDomainTlsProvider
	} from '../../../../lib/types';

	interface Props {
		show: boolean;
		// whether the modal should open directly showing the edit form, rather than the
		// read-only summary of the existing domain/intent (set by the caller before opening)
		startEditing: boolean;
	}

	let { show = $bindable(), startEditing }: Props = $props();

	let isEditing = $state(false);
	let error: string | null = $state(null);
	let loading = $state(false);

	let domain = $state('');
	let tlsProvider: CustomDomainTlsProvider = $state('auto');
	let tlsPrivateKey: string = $state('');
	let tlsCertificate: string = $state('');
	let tlsPrivateKeyError = $state('');
	let tlsCertificateError = $state('');

	let domainInput: HTMLInputElement;

	let customDomain = $derived($hostingInfoStore.custom_domain);
	let intent = $derived($hostingInfoStore.custom_domain_intent);
	let hasExisting = $derived(Boolean(customDomain || intent));

	$effect(() => {
		if (show) {
			domain = customDomain?.domain ?? intent?.domain ?? '';
			tlsProvider = customDomain?.tls_provider ?? 'auto';
			tlsPrivateKey = '';
			tlsCertificate = '';
			tlsPrivateKeyError = '';
			tlsCertificateError = '';
			error = null;
			isEditing = startEditing || !hasExisting;
			domainInput?.focus();
		}
	});

	function applyMutationResult(result: {
		custom_domain: CustomDomainSetup | null;
		custom_domain_intent: CustomDomainIntent | null;
		hosting_info: import('../../../../lib/types').HostingInfo | null;
	}) {
		if (result.hosting_info) {
			updateHostingInfoStore(result.hosting_info);
		} else {
			hostingInfoStore.update((info) => ({
				...info,
				custom_domain: result.custom_domain,
				custom_domain_intent: result.custom_domain_intent
			}));
		}
	}

	async function handleSave() {
		error = null;
		const domainTrimmed = domain.trim();

		if (domainTrimmed === '') {
			error = 'Custom Domain is required';
			return;
		}
		if (domainTrimmed.match(' ')) {
			error = 'Custom Domain cannot contain spaces';
			return;
		}
		if (domainTrimmed.match(/^https?:\/\//)) {
			error = 'Add the domain without the protocol (https://)';
			return;
		}
		if (domainTrimmed.match('/')) {
			error =
				'Custom Domain cannot contain /. Use self-hosting to host your blog in a subdirectory';
			return;
		}

		if (tlsProvider === 'custom') {
			tlsPrivateKeyError = '';
			tlsCertificateError = '';
			if (tlsPrivateKey.trim() === '') {
				tlsPrivateKeyError = 'Private key is required';
			}
			if (tlsCertificate.trim() === '') {
				tlsCertificateError = 'Certificate is required';
			}
			if (tlsPrivateKeyError || tlsCertificateError) {
				return;
			}
		}

		loading = true;
		const saveToastId = toast.loading('Saving custom domain...');

		try {
			if (!hasExisting) {
				const result = await createCustomDomainSetup(
					domainTrimmed,
					tlsProvider,
					tlsProvider === 'custom' ? tlsPrivateKey.trim() : undefined,
					tlsProvider === 'custom' ? tlsCertificate.trim() : undefined
				);
				applyMutationResult(result);
				toast.success(
					tlsProvider === 'custom' ? 'Custom domain is now active!' : 'Custom domain saved!',
					{ id: saveToastId }
				);
			} else {
				const result = await updateCustomDomain({
					newDomain: domainTrimmed,
					tlsProvider,
					tlsPrivateKey: tlsProvider === 'custom' ? tlsPrivateKey.trim() : undefined,
					tlsCertificate: tlsProvider === 'custom' ? tlsCertificate.trim() : undefined
				});
				applyMutationResult(result);
				toast.success('Custom domain updated!', { id: saveToastId });
			}

			isEditing = false;
		} catch (err: any) {
			toast.error(err.message || 'Failed to save domain', { id: saveToastId });
		}
		loading = false;
	}

	async function handleVerify() {
		loading = true;
		const verifyToastId = toast.loading('Verifying DNS records...');

		try {
			const result = await verifyCustomDomainSetup();
			updateHostingInfoStore(result.hosting_info);
			toast.success('Custom domain is verified and active!', { id: verifyToastId });
		} catch (err: any) {
			toast.warning(
				err.message || 'DNS verification failed. Please check your DNS settings and try again.',
				{ id: verifyToastId }
			);
		}
		loading = false;
	}

	async function handleAbortIntent() {
		const stillHasActiveDomain = Boolean(customDomain);

		const confirmAbort = await confirm({
			title: 'Abort Custom Domain Setup',
			content: stillHasActiveDomain
				? `Are you sure you want to abort this setup? Your blog will keep using ${customDomain!.domain} as before.`
				: 'Are you sure you want to abort the custom domain setup? This will revert back to default subdomain setup (hyvorblogs.io) and you will have to start from scratch if you want to set it up again.',
			confirmText: 'Yes, Abort',
			cancelText: 'No, Keep It',
			danger: true
		});

		if (!confirmAbort) {
			return;
		}

		loading = true;
		const abortToastId = toast.loading('Aborting custom domain setup...');

		try {
			await deleteCustomDomainIntent();
			hostingInfoStore.update((info) => ({ ...info, custom_domain_intent: null }));
			toast.success('Custom domain setup aborted', { id: abortToastId });
			if (!stillHasActiveDomain) {
				show = false;
			}
		} catch (err: any) {
			toast.error(err.message || 'Failed to abort custom domain setup', { id: abortToastId });
		}
		loading = false;
	}

	function handleEdit() {
		isEditing = true;
	}

	function handleCancelEdit() {
		if (hasExisting) {
			domain = customDomain?.domain ?? intent?.domain ?? '';
			tlsProvider = customDomain?.tls_provider ?? 'auto';
			isEditing = false;
		} else {
			show = false;
		}
	}
</script>

<Modal title="Custom Domain" {loading} bind:show>
	{#if isEditing}
		<SplitControl label="Custom Domain" noHorizonalPadding>
			<FormControl>
				<TextInput
					bind:value={domain}
					bind:input={domainInput}
					placeholder="blog.example.com"
					block
					state={error ? 'error' : undefined}
					autofocus
				/>
				{#if error}
					<Validation state="error">{error}</Validation>
				{/if}
			</FormControl>
		</SplitControl>

		<SplitControl label="TLS Certificate" noHorizonalPadding>
			<FormControl>
				<Radio name="tls-provider" bind:group={tlsProvider} value="auto">
					Automatic (Recommended)&nbsp;
					<Tooltip
						text="Your TLS certificate will be automatically generated and renewed by Hyvor Blogs using Let's Encrypt."
					>
						<IconInfoCircle size={14} />
					</Tooltip>
				</Radio>

				<Radio name="tls-provider" bind:group={tlsProvider} value="custom">
					Bring Your Own&nbsp;
					<Tooltip text="You can bring your own TLS certificate and private key.">
						<IconInfoCircle size={14} />
					</Tooltip>
				</Radio>
			</FormControl>
		</SplitControl>

		{#if tlsProvider === 'custom'}
			<div transition:slide>
				<SplitControl
					label="Private Key"
					caption="In PEM format, including the BEGIN and END lines"
					noHorizonalPadding
				>
					<FormControl>
						<Textarea
							bind:value={tlsPrivateKey}
							placeholder="-----BEGIN PRIVATE KEY-----"
							block
							state={tlsPrivateKeyError ? 'error' : undefined}
						/>
						{#if tlsPrivateKeyError}
							<Validation state="error">{tlsPrivateKeyError}</Validation>
						{/if}
					</FormControl>
				</SplitControl>

				<SplitControl
					label="Certificate"
					caption="Full certificate chain in PEM format, including the BEGIN and END lines"
					noHorizonalPadding
				>
					<FormControl>
						<Textarea
							bind:value={tlsCertificate}
							placeholder="-----BEGIN CERTIFICATE-----"
							block
							state={tlsCertificateError ? 'error' : undefined}
						/>
						{#if tlsCertificateError}
							<Validation state="error">{tlsCertificateError}</Validation>
						{/if}
					</FormControl>
				</SplitControl>
			</div>
		{:else if tlsProvider !== customDomain?.tls_provider}
			<p class="hint">
				Switching to automatic TLS requires verifying that this domain's DNS points to Hyvor Blogs
				before it can go live.
			</p>
		{/if}
	{:else}
		{#if customDomain}
			<div class="section">
				<div class="section-header">
					<span class="section-domain">{customDomain.domain}</span>
					<Tag color="green" size="small">Active</Tag>
					<span class="section-provider"
						>{customDomain.tls_provider === 'custom' ? 'Bring your own TLS' : 'Automatic TLS'}</span
					>
				</div>
				<DnsInstructions domain={customDomain.domain} />
			</div>
		{/if}

		{#if intent}
			<div class="section" transition:slide>
				<div class="section-header">
					<span class="section-domain">{intent.domain}</span>
					<Tag color="orange" size="small">Pending DNS Validation</Tag>
				</div>
				<p>
					Please configure your DNS records as shown below. Once done, click "Verify Now". We will
					check if the DNS records are set correctly and generate the TLS certificate for your
					custom domain. This may take a few minutes.
				</p>
				<DnsInstructions domain={intent.domain} />
			</div>
		{/if}
	{/if}

	{#snippet footer()}
		{#if isEditing}
			<ButtonGroup>
				<Button variant="invisible" on:click={handleCancelEdit} disabled={loading}>Cancel</Button>
				<Button on:click={handleSave} disabled={loading}>Save</Button>
			</ButtonGroup>
		{:else}
			<ButtonGroup>
				{#if intent}
					<Button variant="fill-light" color="red" on:click={handleAbortIntent} disabled={loading}>
						Abort
					</Button>
				{/if}
				<Button variant="invisible" on:click={handleEdit} disabled={loading}>Edit</Button>
				{#if intent}
					<Button on:click={handleVerify} disabled={loading}>Verify Now</Button>
				{:else}
					<Button on:click={() => (show = false)} disabled={loading}>Close</Button>
				{/if}
			</ButtonGroup>
		{/if}
	{/snippet}
</Modal>

<style>
	.section {
		margin-bottom: 20px;
	}
	.section-header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 8px;
	}
	.section-domain {
		font-weight: 600;
	}
	.section-provider {
		font-size: 12px;
		color: var(--text-light);
	}
	.hint {
		font-size: 13px;
		color: var(--text-light);
	}
</style>
