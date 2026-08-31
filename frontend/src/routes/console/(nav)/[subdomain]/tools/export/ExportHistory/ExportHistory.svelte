<script lang="ts">
	import { IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import type { Export } from '../../../../../lib/types';
	import ExportRow from './ExportRow.svelte';
	import { getExports } from '../exportActions';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let isLoading = $state(true);

	interface Props {
		exports?: Export[];
	}

	let { exports = $bindable([]) }: Props = $props();

	getExports()
		.then((res) => {
			exports = res;
		})
		.catch((err) => toast.error(err.message))
		.finally(() => (isLoading = false));
</script>

{#if isLoading}
	<Loader padding={100} block />
{:else if exports.length === 0}
	<IconMessage empty message={i18n.t('console.tools.export.noExports')} padding={60} />
{:else}
	<Table columns="1fr 1fr 1fr 80px">
		<TableRow head>
			<div>{i18n.t('console.tools.export.format')}</div>
			<div>{i18n.t('console.tools.date')}</div>
			<div>{i18n.t('console.common.status')}</div>
			<div>{i18n.t('console.tools.export.file')}</div>
		</TableRow>

		{#each exports as exp}
			<ExportRow data={exp} />
		{/each}
	</Table>
{/if}
