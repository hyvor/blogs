<script>
	import { onMount } from 'svelte';
	import LanguageRow from './LanguageRow.svelte';
	import { Button, IconButton, TableRow } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import LanguageModal from './LanguageModal.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { cant, redirectIfCant } from '../../../../lib/scope.svelte';
	import UsageLimit from '../../../../lib/components/UsageLimit/UsageLimit.svelte';
	import { orgLimitsStore } from '../../../../lib/stores';

	const i18n = getI18n();

	let isCreating = $state(false);

	onMount(() => {
		redirectIfCant('languages.read');
	});
</script>

<div class="languages">
	<SettingsTop>
		<UsageLimit used={$languagesStore.length} limit={$orgLimitsStore?.languages} focus="languages" />
		<Button disabled={cant('languages.write')} on:click={() => (isCreating = true)}>
			Add Language {#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</SettingsTop>

	<div class="table">
		<SettingsTable columns="1fr 1fr 1fr 70px">
			<TableRow head>
				<div>{i18n.t('console.common.name')}</div>
				<div>{i18n.t('console.settings.languages.code')}</div>
				<div>{i18n.t('console.settings.languages.direction')}</div>
				<div></div>
			</TableRow>

			{#each $languagesStore as language}
				<LanguageRow {language} />
			{/each}
		</SettingsTable>
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
