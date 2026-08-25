<script>
	import LanguageRow from './LanguageRow.svelte';
	import { Button, IconButton, TableRow } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import LanguageModal from './LanguageModal.svelte';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import SettingsTable from '../@components/SettingsTable.svelte';

	let isCreating = $state(false);
</script>

<div class="languages">
	<SettingsTop>
		<Button on:click={() => (isCreating = true)}>
			Add Language {#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</SettingsTop>

	<div class="table">
		<SettingsTable columns="1fr 1fr 1fr 70px">
			<TableRow head>
				<div>Name</div>
				<div>Code</div>
				<div>Direction</div>
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
