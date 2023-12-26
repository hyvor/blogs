<script lang="ts">
	import { Button, Loader } from "@hyvor/design/components";
	import { IconPlus } from "@hyvor/icons";
	import { onMount } from "svelte";
	import consoleApi from "../../lib/consoleApi";
	import type { Post } from "../../lib/types";
	import PostRow from "./PostRow.svelte";

    let isLoading = true;
    let posts: Post[] = [];

    onMount(() => {

        consoleApi.get<Post[]>({
            endpoint: '/posts',
        }).then(res => {
            isLoading = false;
            posts = res;
        })

    });

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

    </div>

    <div class="middle">

        {#if isLoading}
            <div class="loader-wrap">
                <Loader size="large" />
            </div>
        {:else}

            {#each posts as post (post.id)}
                <PostRow {post} />
            {/each}

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

</style>