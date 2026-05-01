<script lang="ts">
	import { TabNav, TabNavItem } from '@hyvor/design/components';
	import NewImport from './NewImport/NewImport.svelte';
	import ImportHistory from './History/ImportHistory.svelte';

	let tab: 'new' | 'history' = $state('new');

	function handleComplete() {
		tab = 'history';
	}
</script>

<div class="import hds-box">
	<TabNav>
		<TabNavItem name="new" active={tab === 'new'} onclick={() => (tab = 'new')}
			>New Import</TabNavItem
		>

		<TabNavItem name="history" active={tab === 'history'} onclick={() => (tab = 'history')}
			>History</TabNavItem
		>
	</TabNav>

	<div class="content">
		{#if tab === 'new'}
			<NewImport on:complete={handleComplete} />
		{:else if tab === 'history'}
			<ImportHistory />
		{/if}
	</div>
</div>

<style>
	.import {
		height: 100%;
		overflow: auto;
		padding: 20px 30px;
	}

	.content {
		padding: 20px 0;
	}
</style>
