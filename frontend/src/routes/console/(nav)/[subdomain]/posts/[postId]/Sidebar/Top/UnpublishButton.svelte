<script lang="ts">
	import { Button, Modal, toast } from "@hyvor/design/components";
	import { postVariantStore } from "../../../postStore";
	import { updatePostVariant } from "../../../postActions";
	import { IconEyeSlash } from "@hyvor/icons";

    let modalOpen = $state(false);

    function handleUnpublish() {

        modalOpen = false;

        const toastId = toast.loading('Unpublishing...');

        updatePostVariant({
            status: 'draft'
        }).then(() => {
            toast.success('Post unpublished', {id: toastId})
        }).catch(() => {
            toast.error('Failed to unpublish post', {id: toastId})
        });

    }

</script>

{#if $postVariantStore.status !== 'draft'}
    {#snippet trigger()}
        <Button
            size="small"
            color="input"
            
            on:click={() => modalOpen = true}
        >
            {#snippet start()}
                <IconEyeSlash  size={12} />
            {/snippet}
            {#if $postVariantStore.status === 'scheduled'}
                Unschedule
            {:else}
                Unpublish
            {/if}
        </Button>
    {/snippet}

    <Modal 
        title={$postVariantStore.status === 'scheduled' ? 'Unschedule Post' : 'Unpublish Post'} 
        bind:show={modalOpen}
        size="small"
    >

        Are you sure to {$postVariantStore.status === 'published' ? 'unpublish' : 'unschedule'} this post? It's status will be changed to draft.

        {#snippet footer()}
                <div >
                <Button variant="invisible" on:click={() => modalOpen = false}>Cancel</Button>
                <Button color="red" on:click={handleUnpublish}>
                    {$postVariantStore.status === 'scheduled' ? 'Unschedule' : 'Unpublish'}
                </Button>
            </div>
            {/snippet}

    </Modal>

{/if}