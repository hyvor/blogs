<script lang="ts">
	import { IconButton, Link, TableRow, Tooltip, confirm, toast } from "@hyvor/design/components";
	import type { Redirect } from "../../../lib/types";
	import { primaryLanguageStore } from "../../../lib/stores/languagesStore";
	import { IconPencilFill, IconTrash } from "@hyvor/icons";
	import { deleteRedirect } from "./redirectActions";
	import { createEventDispatcher } from "svelte";;
	import RedirectsModal from "./RedirectsModal.svelte";

    export let redirect: Redirect;

    let isEditing = false;

    const dispatch = createEventDispatcher();

    async function handleDelete() {
        if (await confirm({
            title: 'Delete redirect',
            content: 'Are you sure you want to delete this redirect?',
            confirmText: 'Yes, delete',
            danger: true,
        })) {

            const toastId = toast.loading('Deleting redirect...');

            deleteRedirect(redirect.id)
                .then(() => {
                    toast.success('redirect deleted.', {id: toastId});
                    dispatch('delete', redirect.id)
                })
                .catch(e => {
                    toast.error(e.message, {id: toastId});
                });
        }
    }

</script>

<TableRow>
    <div>{redirect?.path || 'Unnamed'}</div>
    <div>{redirect?.to || ''}</div>
    <div>{redirect.type}</div>
    <div>
        <Tooltip text="Edit redirect">
            <IconButton 
                variant="fill-light" 
                color="gray" 
                size="small"
                on:click={() => isEditing = true}
            >
                <IconPencilFill size={12} />
            </IconButton>
        </Tooltip>
        <Tooltip text="Delete redirect">  
            <IconButton 
                variant="fill-light" 
                color="red" 
                size="small"
                on:click={handleDelete}
            >
                <IconTrash size={12} />
            </IconButton>
        </Tooltip>
    </div>
</TableRow>

{#if isEditing}
    <RedirectsModal 
        bind:show={isEditing}
        on:update
        {redirect}
    />
{/if}