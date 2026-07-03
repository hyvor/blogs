<script lang="ts">
	import { Callout, Loader, Tag } from '@hyvor/design/components';
	import type { HostingChange, HostingChangeAt } from '../../../../lib/types';
	import { updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import { getHostingInfo } from './hostingActions';
	import { onDestroy } from 'svelte';

	interface Props {
		change: HostingChange;
	}

	let { change }: Props = $props();

	const hostingAtLabels: Record<HostingChangeAt, string> = {
		subdomain: 'Subdomain',
		domain: 'Custom Domain',
		self: 'Self-Hosted'
	};

	// poll faster right after the change is triggered, then back off
	const POLL_DELAYS = [5000, 10000, 20000];

	let pollIndex = 0;
	let timer: ReturnType<typeof setTimeout> | null = null;
	let polling = false;

	function clearTimer() {
		if (timer) {
			clearTimeout(timer);
			timer = null;
		}
	}

	async function poll() {
		timer = null;

		try {
			const info = await getHostingInfo();
			updateHostingInfoStore(info);
		} catch {
			// ignore transient errors, we'll try again on the next tick
		}

		scheduleNextPoll();
	}

	function scheduleNextPoll() {
		clearTimer();

		if (change.status !== 'changing') {
			polling = false;
			return;
		}

		polling = true;
		const delay = POLL_DELAYS[Math.min(pollIndex, POLL_DELAYS.length - 1)];
		pollIndex++;
		timer = setTimeout(poll, delay);
	}

	$effect(() => {
		// re-evaluate whenever the change (or its status) changes, e.g. a new
		// change was just started, or a previous poll resolved it
		change;
		if (change.status === 'changing' && !polling) {
			pollIndex = 0;
			scheduleNextPoll();
		} else if (change.status !== 'changing') {
			clearTimer();
			polling = false;
		}
	});

	onDestroy(clearTimer);

	function loaderState(status: HostingChange['status']) {
		if (status === 'changing') return 'loading';
		if (status === 'success') return 'success';
		return 'error';
	}
</script>

<div class="hosting-change">
	<div class="hosting-change-header">
		<Loader size="small" state={loaderState(change.status)} />
		<span class="hosting-change-title">
			{#if change.status === 'changing'}
				Applying hosting change...
			{:else if change.status === 'success'}
				Hosting change applied
			{:else}
				Hosting change failed
			{/if}
		</span>
	</div>

	<div class="hosting-change-transition">
		<div class="hosting-change-box">
			<Tag size="small" color="default">{hostingAtLabels[change.from_at]}</Tag>
			{#if change.from_url}
				<span class="hosting-change-url">{change.from_url}</span>
			{/if}
		</div>

		<span class="hosting-change-arrow">→</span>

		<div class="hosting-change-box">
			<Tag size="small" color="accent">{hostingAtLabels[change.to_at]}</Tag>
			{#if change.to_url}
				<span class="hosting-change-url">{change.to_url}</span>
			{/if}
		</div>
	</div>

	{#if change.status === 'failed' && change.error_message}
		<Callout type="danger" title="Error">
			{change.error_message}
		</Callout>
	{/if}
</div>

<style>
	.hosting-change {
		display: flex;
		flex-direction: column;
		gap: 12px;
		border: 2px solid var(--border);
		border-radius: var(--box-radius, 8px);
		padding: 16px;
	}
	.hosting-change-header {
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.hosting-change-title {
		font-weight: 600;
	}
	.hosting-change-transition {
		display: flex;
		align-items: center;
		gap: 14px;
		flex-wrap: wrap;
	}
	.hosting-change-box {
		display: flex;
		flex-direction: column;
		gap: 6px;
		flex: 1;
		min-width: 180px;
	}
	.hosting-change-url {
		font-size: 13px;
		color: var(--text-light);
		word-break: break-all;
	}
	.hosting-change-arrow {
		font-size: 20px;
		color: var(--text-light);
	}
</style>
