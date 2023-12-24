<script lang="ts">
	import { Button, Modal, toast } from "@hyvor/design/components";
	import { postVariantStore } from "../../../../../lib/stores/postStore";
	import { updatePostVariant } from "../../../../../lib/actions/postActions";

    let modalOpen = false;

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
    <Button
        size="medium" 
        color="light" 
        slot="trigger"
        on:click={() => modalOpen = true}
    >
        {#if $postVariantStore.status === 'scheduled'}
            Unschedule
        {:else}
            Unpublish
        {/if}
    </Button>

    <Modal 
        title={$postVariantStore.status === 'scheduled' ? 'Unschedule Post' : 'Unpublish Post'} 
        bind:show={modalOpen}
        size="small"
    >

        Are you sure to {$postVariantStore.status === 'published' ? 'unpublish' : 'unschedule'} this post? It's status will be changed to draft.

        <div slot="footer">
            <Button color="invisible" on:click={() => modalOpen = false}>Cancel</Button>
            <Button color="danger" on:click={handleUnpublish}>
                {$postVariantStore.status === 'scheduled' ? 'Unschedule' : 'Unpublish'}
            </Button>
        </div>

    </Modal>

{/if}