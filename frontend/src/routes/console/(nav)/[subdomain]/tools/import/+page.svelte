<script lang="ts">
	import { onMount } from 'svelte';
	import { TabNav, TabNavItem } from '@hyvor/design/components';
	import NewImport from './NewImport/NewImport.svelte';
	import ImportHistory from './History/ImportHistory.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { redirectIfCant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	let tab: 'new' | 'history' = $state('new');

	function handleComplete() {
		tab = 'history';
	}

	onMount(() => {
		redirectIfCant('import.manage');
	});
</script>

<div class="import hds-box">
	<TabNav>
		<TabNavItem name="new" active={tab === 'new'} onclick={() => (tab = 'new')}
			>{i18n.t('console.tools.import.newImport')}</TabNavItem
		>

		<TabNavItem name="history" active={tab === 'history'} onclick={() => (tab = 'history')}
			>{i18n.t('console.tools.history')}</TabNavItem
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
