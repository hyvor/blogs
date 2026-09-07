<script lang="ts">
	import type { HostingChange } from '../../../../lib/types';
	import { updateHostingInfoStore } from '../../../../lib/stores/blogStore';
	import { getHostingInfo } from './hostingActions';
	import HostingChangeRow from './HostingChangeRow.svelte';
	import { onDestroy } from 'svelte';

	interface Props {
		change: HostingChange;
	}

	let { change }: Props = $props();

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
		} catch (e: any) {
			if (e.code === 404 && e.message === 'Blog not found') {
				// this is when the subdomain is changed
				location.href = '/console/' + change.to_subdomain + '/settings/hosting';
				return;
			}
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
</script>

<div class="wrap">
	<HostingChangeRow {change} />
</div>

<style>
	.wrap {
		margin-top: 16px;
	}
</style>
