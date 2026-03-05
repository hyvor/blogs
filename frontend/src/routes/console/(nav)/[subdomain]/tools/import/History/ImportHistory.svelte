<script lang="ts">
	import { IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import type { Import } from '../../../../../lib/types';
	import { getImports } from '../importActions';
	import ImportRow from './ImportRow.svelte';

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
	<IconMessage empty message="No imports found" padding={60} />
{:else}
	<Table columns="2fr 1fr 1fr 1fr 1fr 60px">
		<TableRow head>
			<div>Name/URL</div>
			<div>Type</div>
			<div>Date</div>
			<div>Status</div>
			<div>Counts</div>
			<div>More</div>
		</TableRow>

		{#each imports as imp}
			<ImportRow data={imp} />
		{/each}
	</Table>
{/if}
