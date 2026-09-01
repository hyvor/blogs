<script lang="ts">
	import {
		Button,
		ButtonGroup,
		Callout,
		Modal,
		confirm,
		toast,
		Tag
	} from '@hyvor/design/components';
	import { deleteCustomDomainIntent, verifyCustomDomainSetup } from './hostingActions';
	import { hostingInfoStore, updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import DnsInstructions from './DnsInstructions.svelte';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	let loading = $state(false as boolean | string);

	let intent = $derived($hostingInfoStore.custom_domain_intent);

	interface VerifyError {
		message: string;
		data: { debug_error?: string; debug_logs?: string } | null;
	}

	let verifyError: VerifyError | null = $state(null);
	let showDebugInfo = $state(false);

	// reset the error state whenever the modal is (re)opened
	$effect(() => {
		if (show) {
			verifyError = null;
			showDebugInfo = false;
		}
	});

	// background verification (e.g. the hosting change completing) can clear the
	// intent while this modal is open
	$effect(() => {
		if (show && !intent) {
			show = false;
		}
	});

	async function handleVerify() {
		loading = 'Verifying...';
		verifyError = null;
		showDebugInfo = false;

		try {
			const result = await verifyCustomDomainSetup();
			updateHostingInfoStore(result);
			toast.success('Domain verified! Applying the certificate now...');
		} catch (err: any) {
			verifyError = {
				message:
					err.message || 'DNS verification failed. Please check your DNS settings and try again.',
				data: err.data ?? null
			};
		}
		loading = false;
	}

	async function handleAbortIntent() {
		const confirmAbort = await confirm({
			title: 'Abort Custom Domain Setup',
			content: 'Are you sure you want to abort this custom domain setup?',
			confirmText: 'Yes, Abort',
			cancelText: 'No, Keep It',
			danger: true
		});

		if (!confirmAbort) {
			return;
		}

		loading = 'Aborting...';

		try {
			await deleteCustomDomainIntent();
			hostingInfoStore.update((info) => ({ ...info, custom_domain_intent: null }));
			toast.success('Custom domain setup aborted');
		} catch (err: any) {
			toast.error(err.message || 'Failed to abort custom domain setup');
		}

		show = false;
		loading = false;
	}
</script>

<Modal title="Complete Custom Domain Setup" {loading} bind:show>
	{#if intent && !intent.has_certificate}
		<div class="section">
			<div class="section-header">
				<span class="section-domain">{intent.domain}</span>
				<Tag color="orange" size="small">Pending DNS Validation</Tag>
			</div>
			<p>
				Please add the DNS records as instructed below. Once added, click "Verify Now" to complete
				the setup.
			</p>
			<DnsInstructions domain={intent.domain} />
		</div>
	{:else if intent}
		<div class="section">
			<div class="section-header">
				<span class="section-domain">{intent.domain}</span>
				<Tag color="blue" size="small">Applying</Tag>
			</div>
			<p>The certificate has been generated. The hosting change is now in progress.</p>
		</div>
	{/if}

	{#if verifyError}
		<div class="section">
			<Callout type="danger">{verifyError.message}</Callout>

			{#if verifyError.data?.debug_error}
				<div class="debug-toggle">
					<Button size="small" color="input" on:click={() => (showDebugInfo = !showDebugInfo)}>
						{showDebugInfo ? 'Hide' : 'Show'} Debug Information
						{#snippet end()}
							<IconChevronDown size={14} />
						{/snippet}
					</Button>
				</div>

				{#if showDebugInfo}
					<pre class="debug">{verifyError.data.debug_error}</pre>
					{#if verifyError.data.debug_logs}
						<pre class="debug">{verifyError.data.debug_logs}</pre>
					{/if}
				{/if}
			{/if}
		</div>
	{/if}

	{#snippet footer()}
		<ButtonGroup>
			{#if intent && !intent.has_certificate}
				<Button variant="fill-light" color="red" on:click={handleAbortIntent} disabled={loading}>
					Abort
				</Button>
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
	.debug-toggle {
		margin-top: 8px;
	}
	.debug {
		margin: 0;
		padding: 12px;
		background: var(--bg2);
		border-radius: var(--box-radius);
		font-family: monospace;
		font-size: 13px;
		white-space: pre-wrap;
		word-break: break-word;
		max-height: 300px;
		overflow-y: auto;
	}
</style>
