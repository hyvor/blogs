<script lang="ts">
	import { Button, ButtonGroup, Modal, confirm, toast, Tag } from '@hyvor/design/components';
	import { deleteCustomDomainIntent, verifyCustomDomainSetup } from './hostingActions';
	import { hostingInfoStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import { slide } from 'svelte/transition';
	import DnsInstructions from './DnsInstructions.svelte';

	interface Props {
		show: boolean;
		// called when the user wants to change the domain/TLS settings instead
		onEdit?: () => void;
	}

	let { show = $bindable(), onEdit }: Props = $props();

	let loading = $state(false);

	let customDomain = $derived($hostingInfoStore.custom_domain);
	let intent = $derived($hostingInfoStore.custom_domain_intent);

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
		show = false;
		onEdit?.();
	}
</script>

<Modal title="Complete Custom Domain Setup" {loading} bind:show>
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

	{#snippet footer()}
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
</style>
