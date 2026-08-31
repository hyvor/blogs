<script lang="ts">
	import { onMount } from 'svelte';
	import type { Route } from '../../../../lib/types';
	import { getRoutes } from './routeActions';
	import { Button, IconMessage, Loader, Table, TableRow, toast } from '@hyvor/design/components';
	import SettingsTop from '../@components/SettingsTop.svelte';
	import IconPlus from '@hyvor/icons/IconPlus';
	import RouteRow from './RouteRow.svelte';
	import CreateUpdateRouteModal from './CreateUpdateRouteModal.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		isLoading?: boolean;
		isCreating?: boolean;
	}

	let { isLoading = $bindable(true), isCreating = $bindable(false) }: Props = $props();

	let routes: Route[] = $state([]);

	onMount(() => {
		getRoutes()
			.then((res) => {
				routes = res;
			})
			.catch((err) => {
				toast.error(err.message);
			})
			.finally(() => {
				isLoading = false;
			});
	});

	function handleDelete(id: number) {
		routes = routes.filter((route) => route.id !== id);
	}

	function handleCreate(e: CustomEvent<Route>) {
		routes = [...routes, e.detail];
		isCreating = false;
	}

	function handleUpdate(e: CustomEvent<Route>) {
		routes = routes.map((route) => {
			if (route.id === e.detail.id) {
				return e.detail;
			}
			return route;
		});
	}
</script>

<SettingsTop>
	<Button on:click={() => (isCreating = true)}>
		{i18n.t('console.settings.routes.create')}
		{#snippet end()}
			<IconPlus />
		{/snippet}
	</Button>
</SettingsTop>

<div class="routes">
	{#if isLoading}
		<Loader full />
	{:else if routes.length === 0}
		<IconMessage empty message={i18n.t('console.settings.routes.noRoutes')} />
	{:else}
		<Table columns="1fr 1fr 1fr 1fr 1fr 70px">
			<TableRow head>
				<div>{i18n.t('console.common.name')}</div>
				<div>{i18n.t('console.settings.routes.match')}</div>
				<div>{i18n.t('console.settings.routes.template')}</div>
				<div>{i18n.t('console.settings.routes.postsFilter')}</div>
				<div>{i18n.t('console.settings.routes.contentType')}</div>
				<div></div>
			</TableRow>

			{#each routes as route (route.id)}
				<RouteRow {route} on:delete={() => handleDelete(route.id)} on:update={handleUpdate} />
			{/each}
		</Table>
	{/if}
</div>

{#if isCreating}
	<CreateUpdateRouteModal bind:show={isCreating} on:create={handleCreate} />
{/if}

<style>
	.routes {
		padding: 15px 30px;
		flex: 1;
		overflow: auto;
	}
</style>
