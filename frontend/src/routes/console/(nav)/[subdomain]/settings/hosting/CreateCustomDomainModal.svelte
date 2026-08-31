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
		Radio,
		Tooltip,
		Textarea
	} from '@hyvor/design/components';
	import { createCustomDomainSetup, updateCustomDomain } from './hostingActions';
	import { hostingInfoStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import { slide } from 'svelte/transition';
	import type {
		CustomDomainIntent,
		CustomDomainSetup,
		CustomDomainTlsProvider,
		HostingInfo
	} from '../../../../lib/types';

	interface Props {
		show: boolean;
		// called after a successful create/update, before the modal closes
		onSaved?: () => void;
	}

	let { show = $bindable(), onSaved }: Props = $props();

	let error: string | null = $state(null);
	let loading = $state(false as boolean | string);

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
			setTimeout(() => {domainInput?.focus();}, 0);
		}
	});

	function applyMutationResult(result: {
		custom_domain: CustomDomainSetup | null;
		custom_domain_intent: CustomDomainIntent | null;
		hosting_info: HostingInfo | null;
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

		loading = 'Creating custom domain...';

		try {
			if (!hasExisting) {
				const result = await createCustomDomainSetup(
					domainTrimmed,
					tlsProvider,
					tlsProvider === 'custom' ? tlsPrivateKey.trim() : undefined,
					tlsProvider === 'custom' ? tlsCertificate.trim() : undefined
				);
				applyMutationResult(result);
			} else {
				const result = await updateCustomDomain({
					newDomain: domainTrimmed,
					tlsProvider,
					tlsPrivateKey: tlsProvider === 'custom' ? tlsPrivateKey.trim() : undefined,
					tlsCertificate: tlsProvider === 'custom' ? tlsCertificate.trim() : undefined
				});
				applyMutationResult(result);
				//toast.success('Custom domain updated!', { id: saveToastId });
			}

			show = false;
			onSaved?.();
		} catch (err: any) {
			toast.error(err.message || 'Failed to save domain');
		}
		loading = false;
	}

	function handleCancel() {
		show = false;
	}
</script>

<Modal 
	title={customDomain ? 'Change Custom Domain / TLS' : 'Create Custom Domain'}
	{loading} 
	bind:show
>
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
	{/if}

	{#snippet footer()}
		<ButtonGroup>
			<Button variant="invisible" on:click={handleCancel} disabled={loading}>Cancel</Button>
			<Button on:click={handleSave} disabled={loading}>Save</Button>
		</ButtonGroup>
	{/snippet}
</Modal>

<style>
	.hint {
		font-size: 13px;
		color: var(--text-light);
	}
</style>
