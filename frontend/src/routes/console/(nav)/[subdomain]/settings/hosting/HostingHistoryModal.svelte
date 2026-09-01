<script lang="ts">
	import { Modal, IconMessage, LoadButton, toast } from '@hyvor/design/components';
	import type { HostingChange } from '../../../../lib/types';
	import { getHostingHistory } from './hostingActions';
	import HostingChangeRow from './HostingChangeRow.svelte';

	interface Props {
		show: boolean;
	}

	let { show = $bindable() }: Props = $props();

	const LIMIT = 20;

	let history: HostingChange[] = $state([]);
	let isLoading = $state(false);
	let isLoadingMore = $state(false);
	let hasMore = $state(false);
	let loaded = $state(false);

	function load(more = false) {
		more ? (isLoadingMore = true) : (isLoading = true);
		const offset = more ? history.length : 0;

		getHostingHistory(LIMIT, offset)
			.then((res) => {
				history = more ? [...history, ...res] : res;
				hasMore = res.length === LIMIT;
				loaded = true;
			})
			.catch((err: any) => {
				toast.error(err.message || 'Failed to load hosting history');
			})
			.finally(() => {
				isLoading = false;
				isLoadingMore = false;
			});
	}

	$effect(() => {
		if (show) {
			if (!loaded) {
				load();
			}
		} else {
			loaded = false;
			history = [];
		}
	});
</script>

<Modal title="Hosting History" bind:show loading={isLoading}>
	{#if loaded && history.length === 0}
		<IconMessage empty message="No hosting changes yet" />
	{:else if loaded}
		<div class="list">
			{#each history as change (change.id)}
				<HostingChangeRow {change} />
			{/each}
		</div>
		<LoadButton
			text="Load more"
			show={hasMore}
			loading={isLoadingMore}
			on:click={() => load(true)}
		/>
	{/if}
</Modal>

<style>
	.list {
		display: flex;
		flex-direction: column;
		gap: 12px;
		max-height: 600px;
		overflow: auto;
	}
</style>
