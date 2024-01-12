<script lang="ts">
	import AuthorFilter from './Filters/Author/AuthorFilter.svelte';
	import { Button, IconMessage, LoadButton, Loader, toast } from "@hyvor/design/components";
	import { IconPlus } from "@hyvor/icons";
	import type { Post } from "../../lib/types";
	import PostRow from "./PostRow.svelte";
	import StatusFilter from "./Filters/StatusFilter.svelte";
	import TagFilter from "./Filters/Tag/TagFilter.svelte";
	import DateFilter from "./Filters/Date/DateFilter.svelte";
	import SearchFilter from "./Filters/SearchFilter.svelte";
	import { postListFiltersStore } from "./postListStore";
	import { getPosts } from "./postActions";

    let isLoading = true;
    let isLoadingMore = false;
    let hasMore = false;
    let posts: Post[] = [];
    let error : null | string = null;

    function getTime(date: Date | null) {
        if (!date)
            return undefined;
        return Math.floor(date.getTime() / 1000);
    }

    const limit = 50;

    function loadPosts(more = false) {
        more ? isLoadingMore = true : isLoading = true;
        if (!more) posts = [];
        error = null;
        
        getPosts({
            status: $postListFiltersStore.status || undefined,
            author_id: $postListFiltersStore.author?.id || undefined,
            tag_id: $postListFiltersStore.tag?.id || undefined,
            start_timestamp: getTime($postListFiltersStore.startDate),
            end_timestamp: getTime($postListFiltersStore.endDate),
            search: $postListFiltersStore.search || undefined,
            limit,
            offset: more ? posts.length : 0,
        })
            .then(res => {
                posts = more ? [...posts, ...res] : res;
                hasMore = res.length === limit;
            })
            .catch(() => {
                if (more) 
                    toast.error("Failed to load more posts");
                else error = "Failed to load posts";
            })
            .finally(() => {
                isLoading = false;
                isLoadingMore = false;
            })
    }

    postListFiltersStore.subscribe(() => loadPosts());
</script>


<div id="posts">

    <div class="top">

        <div class="title-wrap">

            <div class="title">Posts</div>
            <div class="">
                <Button size="small">
                    <IconPlus slot="start" />
                    New
                </Button>
            </div>
        </div>

        <div class="filters">
            <StatusFilter />
            <AuthorFilter />
            <TagFilter />
            <DateFilter />
            <SearchFilter />
        </div>

    </div>

    <div class="middle">

        {#if isLoading}
            <div class="loader-wrap">
                <Loader size="large" />
            </div>
        {:else if error}
            <IconMessage error message={error} />
        {:else}

            {#if posts.length === 0}
                <IconMessage empty message="No posts found" />
            {:else}
                {#each posts as post (post.id)}
                    <PostRow {post} />
                {/each}

                <LoadButton
                    text="Load more"
                    show={hasMore}
                    loading={isLoadingMore}
                    on:click={() => loadPosts(true)}
                />

            {/if}

        {/if}

    </div>

</div>


<style>

    #posts {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .top {
        padding: 20px 25px;
        background-color: var(--box-background);
        border-radius: var(--box-radius);
        box-shadow: var(--box-shadow);
        display: flex;
    }

    .middle {
        padding: 20px 25px;
        background-color: var(--box-background);
        border-radius: var(--box-radius);
        box-shadow: var(--box-shadow);
        margin-top: 15px;
        flex: 1;
        overflow: auto;
    }

    .title-wrap {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .loader-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-right: 10px;
    }

    .filters {
        display: flex;
        gap: 7px;
    }

</style>