<script lang="ts">
	import FeaturedChange from './Changes/FeaturedChange.svelte';
	import { postEditingStatusStore, postLanguageStore, updatePostEditingStatusValue } from './../../../../postStore';
	import { Button, ButtonGroup, Modal, SplitControl, Switch, Tag, Validation, toast } from "@hyvor/design/components";
	import { postOriginalStore, postStore, postOriginalVariantStore, postVariantStore } from "../../../../postStore";
	import Diff from "$lib/components/Diff/Diff.svelte";
	import dayjs from "dayjs";
	import { finishUpdating, getPublishedChanges } from "./published-changes";
	import ContentChange from "./Changes/ContentChange.svelte";
	import { updatePost, updatePostAuthors, updatePostTags, updatePostVariant } from "../../../../postActions";
	import CoverImageChange from "./Changes/CoverImageChange.svelte";
	import CanonicalUrlChange from "./Changes/CanonicalUrlChange.svelte";
	import TagChanges from "./Changes/TagChanges.svelte";
	import AuthorChanges from "./Changes/AuthorChanges.svelte";
    export let show = false;

    let changes: ReturnType<typeof getPublishedChanges>;
    let diff = true;
    
    $: $postStore, $postOriginalStore, changes = getPublishedChanges();

    $: disabled = changes.variant.slug !== undefined &&
        ((changes.variant.slug || '').trim() === '' || (changes.variant.slug || '').includes('/'));

    let isLoading = false;

    async function handleUpdate() {
        isLoading = true;

        if (Object.keys(changes.variant).length) {

            try {
                await updatePostVariant({
                    language_id: $postLanguageStore.id,
                    ...changes.variant
                });
            } catch (e: any) {
                return toast.error(e.message);
            }

        }

        if (Object.keys(changes.post).length) {

            try {
                await updatePost({
                    ...changes.post
                });
            } catch (e: any) {
                return toast.error(e.message);
            }

        }

        if (changes.authors !== undefined) {
            try {
                await updatePostAuthors(changes.authors);
            } catch (e: any) {
                return toast.error(e.message);
            }
        }

        if (changes.tags !== undefined) {
            try {
                await updatePostTags(changes.tags);
            } catch (e: any) {
                return toast.error(e.message);
            }
        }

        isLoading = false;
        show = false;

        toast.success('Post updated successfully.');

        finishUpdating();
    }

</script>

<Modal 
    bind:show={show}
    title="Update Post"
    size="medium"
    loading={isLoading}
>

    <div class="note">
        You are about to update the post. Please review the changes below.
    </div>

    <div class="diff">
        <span>
            Show Difference
        </span> <Switch bind:checked={diff} />
    </div>

    {#if changes.variant.content}
        <SplitControl
            label="Content"
        >
            <ContentChange 
                contentOld={$postOriginalVariantStore.content}
                contentNew={$postVariantStore.content_unsaved}
                {diff}
            />
        </SplitControl>
    {/if}

    {#if changes.variant.slug !== undefined}
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

            <div style="margin-top:15px;">
                {#if (changes.variant.slug || '').trim() === ''}
                    <Validation state="error">
                        Slug cannot be empty.
                    </Validation>
                {/if}
                {#if (changes.variant.slug || '').includes('/')}
                    <Validation state="error">
                        Slug cannot contains "/"
                    </Validation>
                {/if}
            </div>

        </SplitControl>
    {/if}

    {#if changes.variant.description !== undefined}
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

    {#if changes.authors !== undefined}
        <AuthorChanges {diff} />
    {/if}

    {#if changes.tags !== undefined}
        <TagChanges {diff} />
    {/if}

    {#if changes.post.featured_image_url !== undefined}
        <SplitControl
            label="Cover Image"
        >
            <CoverImageChange 
                featuredImageOld={$postOriginalStore.featured_image_url}
                featuredImageNew={$postStore.featured_image_url}
                {diff}
            />
        </SplitControl>
    {/if}

    {#if changes.post.published_at !== undefined}
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

    {#if changes.post.is_featured !== undefined}
        <SplitControl
            label="Featured"
        >
            <FeaturedChange 
                old={$postOriginalStore.is_featured}
                {diff}
            />
        </SplitControl>
    {/if}

    {#if changes.post.canonical_url !== undefined}
        <CanonicalUrlChange 
            canonicalUrlOld={$postOriginalStore.canonical_url}
            canonicalUrlNew={$postStore.canonical_url}
            {diff}
        />
    {/if}

    {#if changes.post.code_head !== undefined}
        <SplitControl label="Code Head">
            Changed
        </SplitControl>
    {/if}

    {#if changes.post.code_foot !== undefined}
        <SplitControl label="Code Foot">
            Changed
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
                disabled={disabled}
            >Update</Button>

        </ButtonGroup>

    </svelte:fragment>

</Modal>


<style>
    .note {
        margin-bottom: 15px;
    }
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