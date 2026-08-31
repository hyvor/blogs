<script lang="ts">
	import { IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import type { Import } from '../../../../../lib/types';
	import { getImports } from '../importActions';
	import ImportRow from './ImportRow.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let isLoading = $state(true);

	interface Props {
		imports?: Import[];
	}

	let { imports = $bindable([]) }: Props = $props();

	getImports()
		.then((res) => {
			imports = res;
		})
		.catch((err) => toast.error(err.message))
		.finally(() => (isLoading = false));
</script>

{#if isLoading}
	<Loader padding={100} block />
{:else if imports.length === 0}
	<IconMessage empty message={i18n.t('console.tools.import.noImports')} padding={60} />
{:else}
	<Table columns="2fr 1fr 1fr 1fr 1fr 60px">
		<TableRow head>
			<div>{i18n.t('console.tools.import.nameUrl')}</div>
			<div>{i18n.t('console.tools.import.type')}</div>
			<div>{i18n.t('console.tools.date')}</div>
			<div>{i18n.t('console.common.status')}</div>
			<div>{i18n.t('console.tools.import.counts')}</div>
			<div>{i18n.t('console.tools.import.more')}</div>
		</TableRow>

		{#each imports as imp}
			<ImportRow data={imp} />
		{/each}
	</Table>
{/if}
