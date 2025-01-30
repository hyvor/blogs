<script lang="ts">
	import { Button, IconButton, TableRow, Tag, Tooltip, confirm, toast } from "@hyvor/design/components";
    import type { Route } from "../../../../lib/types";
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteRoute } from "./routeActions";
	import { createEventDispatcher } from "svelte";
	import CreateUpdateRouteModal from "./CreateUpdateRouteModal.svelte";
    
    interface Props {
        route: Route;
    }

    let { route }: Props = $props();

    const dispatch = createEventDispatcher();

    let isUpdating = $state(false);

    async function handleDelete() {
        if (await confirm({
            title: 'Delete Route',
            content: 'Are you sure you want to delete this route?',
            confirmText: 'Yes, Delete',
            danger: true
        })) {

            const toastId = toast.loading("Deleting route");

            deleteRoute(route.id)
                .then(() => {
                    toast.success("Route deleted", { id: toastId });
                })
                .catch(err => {
                    toast.error(err.message, { id: toastId });
                });

            dispatch("delete");
        }
    }

    let isDefaultRoute = $derived(['post', 'page', 'index', 'tag', 'author'].indexOf(route.name) >= 0);

</script>

<TableRow>

    <div>{route.name}</div>
    <div>{route.match}</div>
    <div>{route.template}</div>
    <div>{route.posts_filter || ''}</div>
    <div>{route.content_type || 'text/html'}</div>

    
    <div>
        <Tooltip text="Edit Route">
            <IconButton size="small" variant="fill-light" color="gray" on:click={() => isUpdating = true}>
                <IconPencilFill size={10} />
            </IconButton>
        </Tooltip>

        <Tooltip text={isDefaultRoute ? "Default routes cannot be deleted" : "Delete Route"}>
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
    <CreateUpdateRouteModal
        {route}
        bind:show={isUpdating}
        on:update
    />
{/if}