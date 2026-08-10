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
		Textarea
	} from '@hyvor/design/components';
	import {
		createCustomDomainSetup,
		deleteCustomDomainSetup,
		updateCustomDomainSetup,
		updateCustomDomainCerts,
		verifyCustomDomainSetup
	} from './hostingActions';
	import { hostingInfoStore } from '../../../../lib/stores/blogStore';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { slide } from 'svelte/transition';
	import DnsInstructions from './DnsInstructions.svelte';
	import type { CustomDomainTlsProvider } from '../../../../lib/types';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let domain = $state('');
	let isEditing = $state(!$hostingInfoStore.custom_domain);
	let error: string | null = $state(null);
	let loading = $state(false);

	// only relevant when creating a brand new custom domain
	let tlsProvider: CustomDomainTlsProvider = $state('auto');
	let tlsPrivateKey: string = $state('');
	let tlsCertificate: string = $state('');
	let tlsPrivateKeyError = $state('');
	let tlsCertificateError = $state('');

	// used to rotate the certificate of an already-active "bring your own" domain
	let manageCerts = $state(false);
	let manageCertsPrivateKey = $state('');
	let manageCertsCertificate = $state('');
	let manageCertsPrivateKeyError = $state('');
	let manageCertsCertificateError = $state('');

	let domainInput: HTMLInputElement;

	$effect(() => {
		if (show) {
			domain = $hostingInfoStore.custom_domain?.domain || '';
			tlsProvider = $hostingInfoStore.custom_domain?.tls_provider || 'auto';
			isEditing = !$hostingInfoStore.custom_domain;
			error = null;
			manageCerts = false;
			domainInput?.focus();
		}
	});

	async function handleNext() {
		error = null;
		const domainTrimmed = domain.trim();
		const isNewSetup = !$hostingInfoStore.custom_domain;

		if ($hostingInfoStore.custom_domain?.domain === domainTrimmed) {
			// No changes, proceed to verification
			isEditing = false;
			return;
		}

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

		if (isNewSetup && tlsProvider === 'custom') {
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
			if (isNewSetup) {
				const result = await createCustomDomainSetup(
					domainTrimmed,
					tlsProvider,
					tlsProvider === 'custom' ? tlsPrivateKey.trim() : undefined,
					tlsProvider === 'custom' ? tlsCertificate.trim() : undefined
				);

				hostingInfoStore.update((info) => ({
					...info,
					...(result.hosting_info ?? {}),
					custom_domain: result.custom_domain
				}));

				toast.success(
					tlsProvider === 'custom' ? 'Custom domain is now active!' : 'Custom domain saved!',
					{ id: saveToastId }
				);
			} else {
				const customDomainSetup = await updateCustomDomainSetup(domainTrimmed);
				hostingInfoStore.update((info) => ({ ...info, custom_domain: customDomainSetup }));
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
			const customDomainSetup = await verifyCustomDomainSetup();
			hostingInfoStore.update((info) => ({ ...info, custom_domain: customDomainSetup }));
			if (customDomainSetup.status === 'active') {
				toast.success('Custom domain is verified and active!', { id: verifyToastId });
				show = false;
			} else {
				toast.warning('DNS verification is pending. Please configure your DNS settings.', {
					id: verifyToastId
				});
			}
		} catch (err: any) {
			toast.error(err.message || 'Failed to verify DNS records', { id: verifyToastId });
		}
		loading = false;
	}

	function handleEditDomain() {
		isEditing = true;
	}

	async function handleAbort() {
		const confirmAbort = await confirm({
			title: 'Abort Custom Domain Setup',
			content:
				'Are you sure you want to abort the custom domain setup? This will revert back to default subdomain setup (hyvorblogs.io) and you will have to start from scratch if you want to set it up again.',
			confirmText: 'Yes, Abort',
			cancelText: 'No, Keep It',
			danger: true
		});

		if (!confirmAbort) {
			return;
		}

		loading = true;
		const abortToastId = toast.loading('Aborting custom domain setup...');

		await deleteCustomDomainSetup()
			.then(() => {
				hostingInfoStore.update((info) => ({ ...info, custom_domain: null }));
				toast.success('Custom domain setup aborted', { id: abortToastId });
				isEditing = true;
				show = false;
			})
			.catch((err) => {
				toast.error(err.message || 'Failed to abort custom domain setup', { id: abortToastId });
			})
			.finally(() => {
				loading = false;
			});
	}

	async function handleSaveCerts() {
		manageCertsPrivateKeyError = '';
		manageCertsCertificateError = '';

		if (manageCertsPrivateKey.trim() === '') {
			manageCertsPrivateKeyError = 'Private key is required';
		}
		if (manageCertsCertificate.trim() === '') {
			manageCertsCertificateError = 'Certificate is required';
		}
		if (manageCertsPrivateKeyError || manageCertsCertificateError) {
			return;
		}

		loading = true;
		const toastId = toast.loading('Updating certificate...');

		try {
			const customDomainSetup = await updateCustomDomainCerts(
				manageCertsPrivateKey.trim(),
				manageCertsCertificate.trim()
			);
			hostingInfoStore.update((info) => ({ ...info, custom_domain: customDomainSetup }));
			toast.success('Certificate updated!', { id: toastId });
			manageCerts = false;
			manageCertsPrivateKey = '';
			manageCertsCertificate = '';
		} catch (err: any) {
			toast.error(err.message || 'Failed to update certificate', { id: toastId });
		}
		loading = false;
	}
</script>

<Modal title="Set-up Custom Domain" {loading} bind:show>
	<SplitControl label="Custom Domain" noHorizonalPadding>
		<FormControl>
			<TextInput
				bind:value={domain}
				bind:input={domainInput}
				placeholder="blog.example.com"
				block
				state={error ? 'error' : undefined}
				readonly={!isEditing}
				autofocus
			/>
			{#if error}
				<Validation state="error">{error}</Validation>
			{/if}
		</FormControl>
	</SplitControl>

	{#if isEditing && !$hostingInfoStore.custom_domain}
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
		{/if}
	{/if}

	{#if !isEditing}
		{#if $hostingInfoStore.custom_domain?.status === 'pending'}
			<p>
				Please configure your DNS records as shown below. Once done, click "Verify Now". We will
				check if the DNS records are set correctly and generate the TLS certificate for your custom
				domain. This may take a few minutes.
			</p>
		{:else}
			<p>
				Your custom domain is active. Make sure your DNS records are set as shown below so that
				traffic reaches your blog.
			</p>
		{/if}

		<DnsInstructions {domain} />

		{#if $hostingInfoStore.custom_domain?.status === 'active' && $hostingInfoStore.custom_domain?.tls_provider === 'custom'}
			{#if manageCerts}
				<div transition:slide>
					<SplitControl
						label="Private Key"
						caption="In PEM format, including the BEGIN and END lines"
						noHorizonalPadding
					>
						<FormControl>
							<Textarea
								bind:value={manageCertsPrivateKey}
								placeholder="-----BEGIN PRIVATE KEY-----"
								block
								state={manageCertsPrivateKeyError ? 'error' : undefined}
							/>
							{#if manageCertsPrivateKeyError}
								<Validation state="error">{manageCertsPrivateKeyError}</Validation>
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
								bind:value={manageCertsCertificate}
								placeholder="-----BEGIN CERTIFICATE-----"
								block
								state={manageCertsCertificateError ? 'error' : undefined}
							/>
							{#if manageCertsCertificateError}
								<Validation state="error">{manageCertsCertificateError}</Validation>
							{/if}
						</FormControl>
					</SplitControl>

					<Button on:click={handleSaveCerts} disabled={loading}>Save Certificate</Button>
				</div>
			{/if}
		{/if}
	{/if}

	{#snippet footer()}
		{#if !isEditing}
			<ButtonGroup>
				{#if $hostingInfoStore.custom_domain?.status === 'pending'}
					<Button variant="invisible" on:click={handleEditDomain} disabled={loading}
						>Edit Domain</Button
					>
					<Button variant="fill-light" color="red" on:click={handleAbort} disabled={loading}
						>Abort</Button
					>
					<Button on:click={handleVerify} disabled={loading}>Verify Now</Button>
				{:else}
					{#if $hostingInfoStore.custom_domain?.tls_provider === 'custom'}
						<Button
							variant="invisible"
							on:click={() => (manageCerts = !manageCerts)}
							disabled={loading}
						>
							{manageCerts ? 'Cancel' : 'Manage Certificate'}
						</Button>
					{/if}
					<Button on:click={() => (show = false)} disabled={loading}>Close</Button>
				{/if}
			</ButtonGroup>
		{:else}
			<ButtonGroup>
				<Button variant="invisible" on:click={() => (show = false)} disabled={loading}
					>Cancel</Button
				>
				<Button on:click={handleNext} disabled={loading}>Next</Button>
			</ButtonGroup>
		{/if}
	{/snippet}
</Modal>
