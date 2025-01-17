<script lang="ts">
	import { onMount } from "svelte";
	import type { Route } from "../../../../lib/types";
	import { getRoutes } from "./routeActions";
	import { Button, IconMessage, Loader, Table, TableRow, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import RouteRow from "./RouteRow.svelte";
	import CreateUpdateRouteModal from "./CreateUpdateRouteModal.svelte";

    export let isLoading = true;
    export let isCreating = false;

    let routes : Route[] = [];

    onMount(() => {

        getRoutes()
            .then(res => {
                routes = res;
            })
            .catch(err => {
                toast.error(err.message);
            })
            .finally(() => {
                isLoading = false;
            })

    });

    function handleDelete(id: number) {
        routes = routes.filter(route => route.id !== id);
    }

    function handleCreate(e: CustomEvent<Route>) {
        routes = [...routes, e.detail];
        isCreating = false;
    }

    function handleUpdate(e: CustomEvent<Route>) {
        routes = routes.map(route => {
            if (route.id === e.detail.id) {
                return e.detail;
            }
            return route;
        });
    }

</script>

<SettingsTop>

    <Button on:click={() => isCreating = true}>
        Create Route <IconPlus slot="end" />
    </Button>

</SettingsTop>

<div class="routes">

    {#if isLoading}
        <Loader full />
    {:else}
        {#if routes.length === 0}
            <IconMessage empty message="No Routes" />
        {:else}
            <Table columns="1fr 1fr 1fr 1fr 1fr 70px">

                <TableRow head>
                    <div>Name</div>
                    <div>Match</div>
                    <div>Template</div>
                    <div>Posts Filter</div>
                    <div>Content Type</div>
                    <div/>
                </TableRow>

                {#each routes as route (route.id)}
                    <RouteRow
                        {route}
                        on:delete={() => handleDelete(route.id)}
                        on:update={handleUpdate}
                    />
                {/each}
            
            </Table>
        {/if}
    {/if}

</div>

{#if isCreating}
    <CreateUpdateRouteModal
        bind:show={isCreating}
        on:create={handleCreate}
    />
{/if}

<style>
    .routes {
        padding: 15px 30px;
        flex: 1;
        overflow: auto;
    }
</style>