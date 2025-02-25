<script lang="ts">
	import IconArrowLeft from '@hyvor/icons/IconArrowLeft';
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import IconCardHeading from '@hyvor/icons/IconCardHeading';
	import IconThreeDots from '@hyvor/icons/IconThreeDots';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { Node } from 'prosemirror-model';
	import { tick } from 'svelte';
	import { type Selection } from 'prosemirror-state';
	import { ActionList, ActionListItem, Dropdown } from '@hyvor/design/components';
	import {
		addColumnAfter,
		addColumnBefore,
		deleteColumn,
		deleteTable,
		toggleHeaderColumn
	} from 'prosemirror-tables';
	import schema from '../../schema';
	import { editorStore } from '../../store';

	let { updateId }: { updateId: number } = $props();

	let show = $state(false);
	let wrapEl: HTMLSpanElement | undefined = $state();
	let showDropdown = $state(false);

	function isSelectionInTable(selection: Selection) {
		const pos = selection.$anchor;
		let index = pos.depth;
		while (index > 0) {
			if (pos.node(index).type.name === 'table') {
				return true;
			}
			index--;
		}
		return false;
	}

	async function position() {
		const view = $editorStore.view;
		if (!view) return;

		const selection = view.state.selection;
		const selectionInTable = isSelectionInTable(selection);

		if (selectionInTable) {
			show = true;

			await tick();

			let domNode = view.domAtPos(selection.$anchor.pos).node;
			if (domNode.nodeType === 3) domNode = domNode.parentNode!;
			if (!(domNode instanceof HTMLElement)) return;

			const td = domNode.closest('td, th');
			const table = domNode.closest('table');
			if (!td || !table) return;

			const { left, width } = td.getBoundingClientRect();
			const { top } = table.getBoundingClientRect();

			if (!wrapEl) return;
			const wrapElRect = wrapEl.getBoundingClientRect();
			wrapEl.style.top = top - wrapElRect.height / 2 + 'px';
			wrapEl.style.left = `${left}px`;
			wrapEl.style.width = width + 'px';
		} else {
			show = false;
		}
	}

	function close() {
		showDropdown = false;
		$editorStore.view.focus();
	}

	function handleHeader() {
		toggleHeaderColumn($editorStore.view.state, $editorStore.view.dispatch);
		close();
	}

	function handleInsertBefore() {
		addColumnBefore($editorStore.view.state, $editorStore.view.dispatch);
		close();
	}

	function handleInsertBelow() {
		addColumnAfter($editorStore.view.state, $editorStore.view.dispatch);
		close();
	}

	function handleDelete() {
		function hasOnlyOneColumn(table: HTMLTableElement) {
			const rows = table.getElementsByTagName('tr');
			// Iterate through the rows
			for (var i = 0; i < rows.length; i++) {
				var cells = rows[i]!.getElementsByTagName('td');
				// Check if the row has exactly one cell
				if (cells.length !== 1) {
					return false;
				}
			}
			// If all rows have only one cell, return true
			return true;
		}

		const domNode = $editorStore.view.domAtPos($editorStore.view.state.selection.$anchor.pos).node;
		if (domNode instanceof HTMLElement) {
			const table = domNode.closest('table');
			if (hasOnlyOneColumn(table!)) {
				deleteTable($editorStore.view.state, $editorStore.view.dispatch);
				close();
				return;
			}
		}

		deleteColumn($editorStore.view.state, $editorStore.view.dispatch);
		close();
	}

	function handleClearContent() {
		// TODO: This function is not working properly.
		// So, the button is disabled for now

		function findColumnIndex() {
			let domNode = $editorStore.view.domAtPos($editorStore.view.state.selection.$anchor.pos).node;
			if (domNode.nodeType === 3) domNode = domNode.parentNode!;
			if (domNode instanceof HTMLElement) {
				const td = domNode.closest('td, th');
				if (!td) return;
				const tr = td.closest('tr');
				if (!tr) return;
				const columnIndex = Array.from(tr.children).indexOf(td);
				return columnIndex;
			}

			return null;
		}

		let columnIndex = findColumnIndex();
		if (columnIndex === null) return;

		const pos = $editorStore.view.state.selection.$anchor;
		let index = pos.depth;
		let node: Node;
		let table: Node | null = null;
		while (index > 0) {
			node = pos.node(index);
			if (node.type.name === 'table') {
				table = node;
				break;
			}
			index--;
		}
		if (!table) return;

		const tablePos = pos.before(index);

		const tr = $editorStore.view.state.tr;
		table.descendants((row, pos) => {
			if (row.type.name !== 'table_row') return;
			const rowPos = tablePos + pos;

			//console.log(rowPos, pos);

			row.descendants((cell, pos, _, cellIndex) => {
				if (cell.type.name !== 'table_cell') return;
				if (cellIndex === columnIndex) {
					const cellPos = rowPos + pos;
					tr.replaceWith(
						cellPos,
						cellPos + cell.nodeSize,
						schema.nodes.table_cell!.createAndFill()!
					);
				}
			});
		});

		$editorStore.view.dispatch(tr);

		close();
	}

	$effect(() => {
		updateId;
		position();
	});
</script>

<svelte:window onscrollcapture={position} />

<span bind:this={wrapEl} class:show class="wrap">
	<Dropdown bind:show={showDropdown} align="center">
		{#snippet trigger()}
			<button>
				<IconThreeDots size={14} />
			</button>
		{/snippet}

		{#snippet content()}
			<ActionList>
				<ActionListItem on:click={handleHeader}>
					{#snippet start()}
						<IconCardHeading />
					{/snippet}
					Header column
				</ActionListItem>
				<ActionListItem on:click={handleInsertBefore}>
					{#snippet start()}
						<IconArrowLeft />
					{/snippet}
					Insert before
				</ActionListItem>
				<ActionListItem on:click={handleInsertBelow}>
					{#snippet start()}
						<IconArrowRight />
					{/snippet}
					Insert after
				</ActionListItem>
				<ActionListItem on:click={handleDelete}>
					{#snippet start()}
						<IconTrash />
					{/snippet}
					Delete column
				</ActionListItem>
				<!-- <ActionListItem on:click={handleClearContent}>
                    <IconBackspace slot="start" />
                    Clear content
                </ActionListItem> -->
			</ActionList>
		{/snippet}
	</Dropdown>
</span>

<style>
	.wrap {
		position: fixed;
		z-index: 100;
		display: none;
		align-items: center;
		justify-content: center;
	}

	.wrap.show {
		display: inline-flex;
	}

	button {
		background-color: var(--input);
		width: 28px;
		height: 16px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 5px;
		transition: 0.2s box-shadow;
	}

	button:hover {
		box-shadow: 0 0 0 2px var(--gray-light);
	}
</style>
