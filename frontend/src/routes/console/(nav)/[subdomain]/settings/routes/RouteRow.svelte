<script lang="ts">
	import {
		Button,
		IconButton,
		TableRow,
		Tag,
		Tooltip,
		confirm,
		toast
	} from '@hyvor/design/components';
	import type { Route } from '../../../../lib/types';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteRoute } from './routeActions';
	import { createEventDispatcher } from 'svelte';
	import CreateUpdateRouteModal from './CreateUpdateRouteModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		route: Route;
	}

	let { route }: Props = $props();

	const dispatch = createEventDispatcher();

	let isUpdating = $state(false);

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.routes.deleteTitle'),
				content: i18n.t('console.settings.routes.deleteContent'),
				confirmText: i18n.t('console.settings.routes.deleteConfirm'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.routes.deleting'));

			deleteRoute(route.id)
				.then(() => {
					toast.success(i18n.t('console.settings.routes.deleted'), { id: toastId });
				})
				.catch((err) => {
					toast.error(err.message, { id: toastId });
				});

			dispatch('delete');
		}
	}

	let isDefaultRoute = $derived(
		['post', 'page', 'index', 'tag', 'author'].indexOf(route.name) >= 0
	);
</script>

<TableRow>
	<div>{route.name}</div>
	<div>{route.match}</div>
	<div>{route.template}</div>
	<div>
		{#if route.posts_filter === null}
			<span class="filter-tag">{i18n.t('console.common.disabled')}</span>
		{:else if route.posts_filter === ''}
			<span class="filter-tag">{i18n.t('console.settings.routes.allPosts')}</span>
		{:else}
			{route.posts_filter}
		{/if}
	</div>
	<div>{route.content_type || 'text/html'}</div>

	<div>
		<Tooltip text={i18n.t('console.settings.routes.editRoute')}>
			<IconButton
				size="small"
				variant="fill-light"
				color="gray"
				on:click={() => (isUpdating = true)}
			>
				<IconPencilFill size={10} />
			</IconButton>
		</Tooltip>

		<Tooltip
			text={isDefaultRoute
				? i18n.t('console.settings.routes.cannotDeleteDefault')
				: i18n.t('console.settings.routes.deleteRoute')}
		>
			<IconButton
				size="small"
				variant="fill-light"
				color="red"
				on:click={handleDelete}
				disabled={isDefaultRoute}
			>
				<IconTrash size={10} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

{#if isUpdating}
	<CreateUpdateRouteModal {route} bind:show={isUpdating} on:update />
{/if}

<style>
	.filter-tag {
		color: var(--text-light);
		font-size: 14px;
	}
</style>
