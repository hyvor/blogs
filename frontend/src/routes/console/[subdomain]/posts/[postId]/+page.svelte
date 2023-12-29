<script lang="ts">
    import { page } from '$app/stores'
	import { IconButton, Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { scale } from 'svelte/transition';
	import consoleApi from "../../../lib/consoleApi";
	import type { Post } from "../../../lib/types";
	import { initPostEditingState, postOriginalStore, postStore, setPostAndPostOriginalStore } from "../../../lib/stores/postStore";
	import PostHeader from "./Post/PostHeader.svelte";
	import PostBody from "./Post/PostBody.svelte";
	import PostSidebar from "./Post/Sidebar/PostSidebar.svelte";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { IconCaretLeftFill } from "@hyvor/icons";
    const postId = $page.params.postId;

    let isLoading = true;

    onMount(() => {

        consoleApi.get<Post>({
            endpoint: '/post/' + postId,
        }).then(res => {

            setPostAndPostOriginalStore(res);
            initPostEditingState();

            isLoading = false;
        })

    });

</script>

<div 
    id="post-view"
    in:scale={{ duration: 300, opacity: 0, start: 0.8 }}
>

    <a 
        href="/console/{$blogStore.subdomain}/posts"
        class="back"
    >
        <IconButton color="soft">
            <IconCaretLeftFill />
        </IconButton>
    </a>

    {#if isLoading}

        <div class="full-loader">
            <Loader block size="large" />
        </div>

    {:else}

        <div class="post-inner">
            
            <div class="post-left">
                <PostHeader />
                <PostBody />
            </div>

            <div class="post-right">
                <PostSidebar />
            </div>

        </div>

    {/if}

</div>

<style>

    #post-view {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: var(--accent-lightest);
        overflow: auto;
        padding: 20px 0;
    }

    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .post-inner {
        width: 1200px;
        margin: auto;
        display: flex;
        align-items: flex-start;
    }

    .post-left {
        width: 700px;
        min-height: calc(100vh - 40px);
        position: relative;
    }

    .post-right {
        flex: 1;
        margin-left: 15px;
        height: calc(100vh - 40px);
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0px;
        z-index: 10;
        min-width: 0;
    }

    .back {
        margin-left: 15px;
        margin-top: 15px;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        font-weight: 600;
        width: 35px;
        height: 35px;
    }

</style>