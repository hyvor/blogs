<script lang="ts">
	import { IconButton, Link, TableRow, Tooltip, confirm, toast } from "@hyvor/design/components";
	import type { Navigation } from "../../../lib/types";
	import { primaryLanguageStore } from "../../../lib/stores/languagesStore";
	import { IconPencilFill, IconTrash } from "@hyvor/icons";
	import { deleteNavigation } from "./navigationActions";
	import { createEventDispatcher } from "svelte";
	import UpdateNavigationModal from "./UpdateNavigationModal.svelte";

    export let navigation: Navigation;

    $: variant = navigation.variants.find(v => v.language_id === $primaryLanguageStore.id);

    let isEditing = false;

    const dispatch = createEventDispatcher();

    async function handleDelete() {
        if (await confirm({
            title: 'Delete navigation',
            content: 'Are you sure you want to delete this navigation?',
            confirmText: 'Yes, delete',
            danger: true,
        })) {

            const toastId = toast.loading('Deleting navigation...');

            deleteNavigation(navigation.id)
                .then(() => {
                    toast.success('Navigation deleted.', {id: toastId});
                    dispatch('delete', navigation.id)
                })
                .catch(e => {
                    toast.error(e.message, {id: toastId});
                });

        }
    }

</script>

<TableRow>
    <div>{variant?.name || 'Unnamed'}</div>
    <div>{navigation?.url || ''}</div>
    <div>
        <Tooltip text="Edit navigation">
            <IconButton 
                variant="fill-light" 
                color="gray" 
                size="small"
                on:click={() => isEditing = true}
            >
                <IconPencilFill size={12} />
            </IconButton>
        </Tooltip>
        <Tooltip text="Delete navigation">  
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
    <UpdateNavigationModal 
        bind:show={isEditing}
        {navigation}
        on:variantCreate
        on:update
    />
{/if}