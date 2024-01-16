<script lang="ts">
	import { Button, ButtonGroup, Modal, SplitControl, Switch, Tag } from "@hyvor/design/components";
	import { postOriginalStore, postStore, postOriginalVariantStore, postVariantStore } from "../../../../postStore";
	import Diff from "$lib/components/Diff/Diff.svelte";
	import dayjs from "dayjs";
	import { getPublishedChanges } from "./published-changes";
	import { getTextFromContent } from "../../../../../../lib/prosemirror/helpers";
    export let show = false;

    let changes: ReturnType<typeof getPublishedChanges>;
    let diff = true;
    
    $: $postStore, $postOriginalStore, changes = getPublishedChanges();


    function handleUpdate() {

    }

</script>

<Modal 
    bind:show={show}
    title="Update Post"
    size="large"
>

    <p>
        You are about to update the post. Please review the changes below.
    </p>

    <div class="diff">
        <span>
            Show Difference
        </span> <Switch bind:checked={diff} />
    </div>

    {#if changes.variant.slug}
        <SplitControl
            label="Slug"
        >
            {#if diff}
                <Diff
                    strOld={$postOriginalVariantStore.slug || ''}
                    strNew={$postVariantStore.slug || ''}
                />
            {:else}
                <span>{$postVariantStore.slug}</span>
            {/if}
        </SplitControl>
    {/if}


    {#if changes.variant.content}
        <SplitControl
            label="Content"
        >
            <Diff 
                strOld={getTextFromContent($postOriginalVariantStore.content)}
                strNew={getTextFromContent($postVariantStore.content)}
            />
        </SplitControl>
    {/if}

    {#if changes.variant.description}
        <SplitControl
            label="Description"
        >
            {#if diff}
                <Diff
                    strOld={$postOriginalVariantStore.description || ''}
                    strNew={$postVariantStore.description || ''}
                />
            {:else}
                <span>{$postVariantStore.description}</span>
            {/if}
        </SplitControl>
    {/if}

    {#if changes.post.published_at}
        <SplitControl
            label="Publish Time"
        >
            {#if diff}
                {
                    $postOriginalStore.published_at ?
                    dayjs.unix($postOriginalStore.published_at).format('YYYY-MM-DD HH:mm:ss') :
                    'None'
                }
                <span> → </span>
                <strong>
                    {
                        $postStore.published_at ?
                        dayjs.unix($postStore.published_at).format('YYYY-MM-DD HH:mm:ss') :
                        'None'
                    }
                </strong>
            {:else}
                <span>
                    {
                        $postStore.published_at ?
                        dayjs.unix($postStore.published_at).format('YYYY-MM-DD HH:mm:ss') :
                        'None'
                    }
                </span>
            {/if}
        </SplitControl>
    {/if}
    
    <svelte:fragment slot="footer">

        <ButtonGroup>

            <Button 
                variant="invisible"
                on:click={() => show = false}
            >Cancel</Button>

            <Button
                on:click={handleUpdate}
            >Update</Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>


<style>
    .diff {
        text-align: center;
        padding: 10px 15px;
        font-size: 14px;
        color: var(--text-light);
    }
    .diff span {
        margin-right: 10px;
    }
</style>