<script lang="ts">
	import { Button, IconMessage, LoadButton, Loader, Table, TableRow, toast } from "@hyvor/design/components";
	import SettingsTop from "../@components/SettingsTop.svelte";
	import { IconPlus } from "@hyvor/icons";
	import type { Tag, TagVariant } from "../../../../lib/types";
	import { onMount } from "svelte";
	import { getTags } from "./tagActions";
	import TagRow from "./TagRow.svelte";
	import CreateTagModal from "./CreateTagModal.svelte";

    let isCreating = $state(false);
    
    let tags : Tag[] = $state([]);
    let isLoading = $state(true);
    let isLoadingMore = $state(true);
    let hasMore = $state(false);

    const limit = 40;

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

    function handleCreate(e: CustomEvent<Tag>) {
        tags = [e.detail, ...tags];
    }

    function handleDelete(e: CustomEvent<number>) {
        tags = tags.filter(t => t.id !== e.detail);
    }

    function handleCreateVariant(e: CustomEvent<{id: number, variant: TagVariant}>) {
        tags = tags.map(t => {
            const newTag = t.id === e.detail.id ? 
                {...t, variants: [...t.variants, e.detail.variant]} : 
                t;
            return newTag;
        });
    }

    function handleUpdate(e: CustomEvent<Tag>) {
        tags = tags.map(t => t.id === e.detail.id ? e.detail : t);
    }

    onMount(loadTags);

</script>


<div class="tags">

    <SettingsTop>
        <Button on:click={() => isCreating = true}>
            Create Tag {#snippet end()}
                        <IconPlus  />
                    {/snippet}
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
                        <div></div>
                    </TableRow>

                    {#each tags as tag (tag.id)}
                        <TagRow 
                            {tag}
                            on:delete={handleDelete}
                            on:variantCreate={handleCreateVariant}
                            on:update={handleUpdate}
                        />
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
    <CreateTagModal 
        bind:show={isCreating}
        on:create={handleCreate}
    />
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