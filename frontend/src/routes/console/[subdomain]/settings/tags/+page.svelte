<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, Table, TableRow, toast } from "@hyvor/design/components";
	import SettingsTop from "../_components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import type { Tag } from "../../../lib/types";
	import { onMount } from "svelte";
	import { getTags } from "./tagActions";
	import TagRow from "./TagRow.svelte";
	import TagModal from "./TagModal.svelte";

    let isCreating = false;
    
    let tags : Tag[] = [];
    let isLoading = true;
    let isLoadingMore = true;
    let hasMore = false;

    const limit = 2;

    function loadTags(more = false) {
        more ? isLoadingMore = true : isLoading = true;

        getTags({
            limit,
            offset: more ? tags.length : 0,
        })
            .then(res => {
                tags = more ? [...tags, ...res] : res;
                hasMore = res.length === limit;
            })
            .catch(e => {
                if (!more) tags = [];
                toast.error(e.message || "Failed to load tags.");
            })
            .finally(() => {
                isLoading = false;
                isLoadingMore = false;
            })
    }

    onMount(loadTags);

</script>


<div class="tags">

    <SettingsTop>
        <Button on:click={() => isCreating = true}>
            Create Tag <IconPlus slot="end" />
        </Button>
    </SettingsTop>

    <div class="note">
        Tags can be used to categorize your posts.
    </div>

    <div class="table">

        {#if isLoading}
            <Loader full /> 
        {:else}
        
            {#if tags.length === 0}
                <IconMessage empty message="No tags found." />
            {:else}
                <Table columns="2fr 2fr 3fr 1fr 70px">

                    <TableRow head>
                        <div>Name</div>
                        <div>Slug/URL</div>
                        <div>Description</div>
                        <div>Posts</div>
                        <div />
                    </TableRow>

                    {#each tags as tag (tag.id)}
                        <TagRow {tag} />
                    {/each}

                    <LoadButton
                        text="Load More"
                        show={hasMore}
                        on:click={() => loadTags(true)}
                        loading={isLoadingMore}
                    />

                </Table>
            {/if}

        {/if}

    </div>

</div>

{#if isCreating}
    <TagModal bind:show={isCreating} />
{/if}

<style>

    .tags {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: auto;
    }

    .note {
        padding: 10px 30px;
        font-size: 14px;
        color: var(--text-light);
    }

    .table {
        flex: 1;
        padding: 15px 30px;
    }

</style>