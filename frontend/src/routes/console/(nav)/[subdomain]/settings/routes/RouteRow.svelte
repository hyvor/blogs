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
				title: i18n.t('console.settings.routes.deleteRoute'),
				content: i18n.t('console.settings.routes.deleteContent'),
				confirmText: i18n.t('console.tools.media.delete.confirm'),
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
	<div>
		<div class="name">{route.name}</div>
		<div class="meta-row">
			<code class="match">{route.match}</code>
			<Tag size="x-small" color="default">{route.content_type || 'text/html'}</Tag>
		</div>
	</div>
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

	<div>
		<Tooltip text={i18n.t('console.settings.routes.editRoute')}>
			<IconButton size="small" color="input" variant="fill" on:click={() => (isUpdating = true)}>
				<IconPencilFill size={10} />
			</IconButton>
		</Tooltip>

		<Tooltip text={isDefaultRoute ? 'Default routes cannot be deleted' : 'Delete Route'}>
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

	.meta-row {
		margin-top: 4px;
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 4px;
	}

	.match {
		font-family: var(--font-monospace, monospace);
		font-size: 12px;
		color: var(--text-light);
		background: var(--input);
		padding: 2px 6px;
		border-radius: 4px;
	}
</style>
