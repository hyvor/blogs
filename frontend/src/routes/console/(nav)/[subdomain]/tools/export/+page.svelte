<script lang="ts">
	import { TabNav, TabNavItem } from '@hyvor/design/components';
	import NewExport from './NewExport.svelte';
	import ExportHistory from './ExportHistory/ExportHistory.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let tab: 'new' | 'history' = $state('new');

	function handleComplete() {
		tab = 'history';
	}
</script>

<div class="export hds-box">
	<TabNav>
		<TabNavItem name="new" active={tab === 'new'} onclick={() => (tab = 'new')}
			>{i18n.t('console.tools.export.newExport')}</TabNavItem
		>
		<TabNavItem name="history" active={tab === 'history'} onclick={() => (tab = 'history')}
			>{i18n.t('console.tools.history')}</TabNavItem
		>
	</TabNav>

	<div class="content">
		{#if tab === 'new'}
			<NewExport on:complete={handleComplete} />
		{:else}
			<ExportHistory />
		{/if}
	</div>
</div>

<style>
	.export {
		height: 100%;
		overflow: auto;
		padding: 20px 30px;
	}
	.content {
		padding: 20px 0;
	}
</style>
