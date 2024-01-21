<script lang="ts">
    import { page } from '$app/stores'
	import { IconButton, Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { scale } from 'svelte/transition';
	import consoleApi from "../../../lib/consoleApi";
	import type { Post } from "../../../lib/types";
	import { initPostEditingState, setPostAndPostOriginalStore, postStore } from "../postStore";
	import PostBody from "./Body/PostBody.svelte";
	import PostSidebar from "./Sidebar/PostSidebar.svelte";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { IconCaretLeftFill } from "@hyvor/icons";
	import { initEditorEventHandlers } from "./Body/Editor/editorEvents";
	import { isTempStore } from "../../../lib/temp";
	import { consoleUrlWithBlog } from "../../../lib/consoleUrl";
    
    const postId = $page.params.postId;

    let isLoading = true;

    let postView : HTMLDivElement;

    onMount(() => {

        consoleApi.get<Post>({
            endpoint: '/post/' + postId,
        }).then(res => {

            setPostAndPostOriginalStore(res);
            initPostEditingState(postView);
            initEditorEventHandlers();

            isLoading = false;
        })

    });

    function getBackUrl() {
        const postData = $postStore;
        return consoleUrlWithBlog(
            (postData && postData.is_page ? '/pages' : '/posts')
        );
    }

</script>

<div 
    id="post-view"
    class:is-temp={$isTempStore}
    in:scale={{ duration: 300, opacity: 0, start: 0.8 }}
    bind:this={postView}
>

    <a 
        href={getBackUrl()}
        class="back"
    >
        <IconButton 
            variant="invisible"
            color="gray"
        >
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
                <!-- <PostHeader /> -->
                <PostBody />
            </div>

            <div class="post-right">
                <PostSidebar />
            </div>

        </div>

    {/if}

</div>

<style lang="scss">

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


    #post-view.is-temp {
        height: calc(100% - var(--top-offset));
        top: var(--top-offset);
        .back {
            top: var(--top-offset);
        }
    }

</style>