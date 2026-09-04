<script lang="ts">
	import { SOURCES, TRIGGERS, dndzone } from 'svelte-dnd-action';
	import { IconButton, TableRow, Tooltip, confirm, toast } from '@hyvor/design/components';
	import SettingsTable from '../@components/SettingsTable.svelte';
	import type { Navigation } from '../../../../lib/types';
	import IconGripVertical from '@hyvor/icons/IconGripVertical';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteNavigation, saveSort } from './navigationActions';
	import { createEventDispatcher } from 'svelte';
	import { flip } from 'svelte/animate';
	import UpdateNavigationModal from './UpdateNavigationModal.svelte';
	import { getI18n } from '../../../../lib/i18n';
	import { cant } from '../../../../lib/scope.svelte';

	const i18n = getI18n();

	interface Props {
		items: Navigation[];
	}

	let { items = $bindable() }: Props = $props();

	let isEditing = $state(false);
	let editingNavigation: null | Navigation = $state(null);

	const flipDurationMs = 200;

	const dispatch = createEventDispatcher();

	async function handleDelete(id: number) {
		if (
			await confirm({
				title: i18n.t('console.settings.navigation.deleteNavigation'),
				content: i18n.t('console.settings.navigation.deleteContent'),
				confirmText: i18n.t('console.common.yesDelete'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.navigation.deleting'));

			deleteNavigation(id)
				.then(() => {
					toast.success(i18n.t('console.settings.navigation.deleted'), { id: toastId });
					dispatch('delete', id);
				})
				.catch((e) => {
					toast.error(e.message, { id: toastId });
				});
		}
	}

	let dragDisabled = $state(true);

	function startDrag(e: any) {
		if (cant('navigations.write')) return;
		// preventing default to prevent lag on touch devices (because of the browser checking for screen scrolling)
		e.preventDefault();
		dragDisabled = false;
	}
	function handleKeyDown(e: any) {
		if (cant('navigations.write')) return;
		if ((e.key === 'Enter' || e.key === ' ') && dragDisabled) dragDisabled = false;
	}

	function handleConsider(e: any) {
		const {
			items: newItems,
			info: { source, trigger }
		} = e.detail;
		items = [...newItems];
		// Ensure dragging is stopped on drag finish via keyboard
		if (source === SOURCES.KEYBOARD && trigger === TRIGGERS.DRAG_STOPPED) {
			dragDisabled = true;
		}
	}

	function handleFinalize(e: any) {
		const {
			items: newItems,
			info: { source }
		} = e.detail;
		items = [...newItems];
		saveOrder(newItems);

		// Ensure dragging is stopped on drag finish via pointer (mouse, touch)
		if (source === SOURCES.POINTER) {
			dragDisabled = true;
		}
	}

	function saveOrder(newItems: Navigation[]) {
		saveSort(newItems.map((blog) => blog.id));
	}
</script>

<SettingsTable columns="70px 1fr 1fr 70px">
	<TableRow head>
		<div></div>
		<div>{i18n.t('console.common.name')}</div>
		<div>{i18n.t('console.tools.import.url')}</div>
		<div></div>
	</TableRow>

	<div
		use:dndzone={{
			items: items,
			type: 'header',
			dragDisabled,
			flipDurationMs,
			dropTargetStyle: {
				outline: 'none',
				background: 'var(--hover)'
			}
		}}
		onfinalize={handleFinalize}
		onconsider={handleConsider}
	>
		{#each items as item (item.id)}
			<div class="global-navigation-item-row" animate:flip={{ duration: flipDurationMs }}>
				<button
					class="dragger"
					style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
					tabindex={dragDisabled ? 0 : -1}
					aria-label="drag-handle"
					onmousedown={startDrag}
					ontouchstart={startDrag}
					onkeydown={handleKeyDown}
					onclick={(e) => e.preventDefault()}
				>
					<IconGripVertical />
				</button>
				<div>{item.variants[0]?.name || 'Unnamed'}</div>
				<div>{item.url}</div>
				<div>
					<Tooltip text={i18n.t('console.settings.navigation.editNavigation')}>
						<IconButton
							color="input"
							variant="fill"
							size="small"
							disabled={cant('navigations.write')}
							on:click={() => {
								isEditing = true;
								editingNavigation = item;
							}}
						>
							<IconPencilFill size={12} />
						</IconButton>
					</Tooltip>
					<Tooltip text={i18n.t('console.settings.navigation.deleteNavigation')}>
						<IconButton
							variant="fill-light"
							color="red"
							size="small"
							disabled={cant('navigations.write')}
							on:click={() => handleDelete(item.id)}
						>
							<IconTrash size={12} />
						</IconButton>
					</Tooltip>
				</div>
			</div>
		{/each}
	</div>
</SettingsTable>

{#if isEditing && editingNavigation}
	<UpdateNavigationModal
		bind:show={isEditing}
		navigation={editingNavigation}
		on:variantCreate
		on:update
	/>
{/if}

<style>
	:global(.global-navigation-item-row) {
		display: grid;
		padding: 14px 20px;
		align-items: center;
		grid-template-columns: 70px 1fr 1fr 70px;
		border-bottom: 1px solid #f1f1f1;
	}

	:global(.global-navigation-item-row:last-child) {
		border-bottom: none;
	}
</style>
