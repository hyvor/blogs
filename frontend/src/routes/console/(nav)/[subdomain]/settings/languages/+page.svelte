<script>
	import LanguageRow from './LanguageRow.svelte';
	import { Button, IconButton, Table, TableRow } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import LanguageModal from './LanguageModal.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	let isCreating = $state(false);
</script>

<div class="languages">
	<SettingsTop>
		<Button on:click={() => (isCreating = true)}>
			{i18n.t('console.settings.languages.add')}
			{#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</SettingsTop>

	<div class="table">
		<Table columns="1fr 1fr 1fr 70px">
			<TableRow head>
				<div>{i18n.t('console.common.name')}</div>
				<div>{i18n.t('console.settings.languages.code')}</div>
				<div>{i18n.t('console.settings.languages.direction')}</div>
				<div></div>
			</TableRow>

			{#each $languagesStore as language}
				<LanguageRow {language} />
			{/each}
		</Table>
	</div>
</div>

{#if isCreating}
	<LanguageModal bind:show={isCreating} />
{/if}

<style>
	.table {
		padding: 15px 30px;
	}
</style>
