<script>
	import { Button, SplitControl } from "@hyvor/design/components";
	import { IconTrash } from "@hyvor/icons";
	import ClearCacheModal from "./ClearCacheModal.svelte";
	import DeleteBlogModal from "./DeleteBlogModal.svelte";

    let isCacheClearing = false;
    let isDeleting = false;
</script>

<div class="danger">

    <SplitControl
        label="Clear Cache"
    >
        <div slot="caption">
            Clear the cache of your blog. This will <strong>not</strong> delete any data.
        </div>

        <Button
            on:click={() => isCacheClearing = true}
        >
            Clear Cache
        </Button>

    </SplitControl>

    <SplitControl
        label="Delete Blog"
    >
        <div slot="caption">
            Completely delete the blog and all its data. This action is <strong>irreversible</strong>.
        </div>

        <Button
            color="red"
            on:click={() => isDeleting = true}
        >
            <IconTrash slot="start" />
            Delete Blog
        </Button>

    </SplitControl>

</div>

{#if isCacheClearing}
    <ClearCacheModal bind:show={isCacheClearing} />
{/if}

{#if isDeleting}
    <DeleteBlogModal bind:show={isDeleting} />
{/if}

<style>
    .danger {
        padding: 20px 30px;
    }
    div[slot="caption"] {
        color: var(--text-light);
        font-size: 14px;
    }
</style>