<script lang="ts">
	import { Table } from '@hyvor/design/components';
	import type { Snippet } from 'svelte';

	interface Props {
		columns: string;
		hover?: boolean;
		children?: Snippet;
	}

	let { columns, hover = true, children }: Props = $props();
</script>

<div class="settings-table">
	<Table {columns} {hover}>
		{@render children?.()}
	</Table>
</div>

<style>
	.settings-table :global([role='row']) {
		border-radius: 0;
		padding: 14px 20px;
		border-bottom: 1px solid #f1f1f1;
	}
	.settings-table :global([role='row'].head) {
		background: transparent;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: var(--text-light);
		padding: 10px 20px;
		margin-bottom: 0;
	}
	.settings-table :global([role='row']:last-child) {
		border-bottom: none;
	}
	.settings-table :global([role='row'] > div > div) {
		border-bottom: none !important;
		padding: 0 !important;
	}
</style>
