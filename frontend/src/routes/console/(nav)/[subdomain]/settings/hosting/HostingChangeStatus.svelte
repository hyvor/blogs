<script lang="ts">
	import { Callout } from '@hyvor/design/components';
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

	let calloutConfig: {
		type: 'info' | 'success' | 'danger';
		content: string;
	} = $derived.by(() => {
		if (change.status === 'changing') {
			return {
				type: 'info',
				content:
					'Your hosting change is being applied. This may take a few minutes depending on the number of posts on your blog. You can leave this page and come back later to check the status.'
			};
		} else if (change.status === 'success') {
			return {
				type: 'success',
				content: 'Your hosting change has been successfully applied.'
			};
		}

		return {
			type: 'danger',
			content:
				change.error_message || 'An unknown error occurred while applying your hosting change.'
		};
	});
</script>

<div class="wrap">
	<div class="transition">
		<a href={change.from_url} target="_blank">{change.from_url}</a>
		({hostingAtLabels[change.from_at]}) &rarr;
		<a href={change.to_url} target="_blank">{change.to_url}</a>
		({hostingAtLabels[change.to_at]})
	</div>
	<div class="date">
		{new Date(change.created_at * 1000).toLocaleString(undefined, {
			dateStyle: 'medium',
			timeStyle: 'short'
		})}
	</div>
	<div class="callout">
		<Callout type={calloutConfig.type}>
			{calloutConfig.content}
		</Callout>
	</div>
</div>

<style>
	.wrap {
		margin-top: 16px;
		border: 1px solid var(--border);
		padding: 16px;
		border-radius: var(--box-radius);
	}
	.date {
		font-size: 12px;
		color: var(--text-light);
		margin-top: 2px;
	}
	.transition {
		line-height: normal;
		font-size: 14px;
	}
	.callout {
		font-size: 14px;
		margin-top: 10px;
		line-height: 14px;
	}
	a {
		text-decoration: underline;
	}
</style>
