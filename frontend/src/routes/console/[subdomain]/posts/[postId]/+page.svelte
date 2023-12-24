<script lang="ts">
    import { page } from '$app/stores'
	import { Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { scale } from 'svelte/transition';
	import consoleApi from "../../../lib/consoleApi";
    const postId = $page.params.postId;

    let isLoading = true;

    onMount(() => {

        consoleApi.get({
            endpoint: '/posts/' + postId,
        })

    });

</script>

<div 
    id="post-view"
    in:scale={{ duration: 300, opacity: 0, start: 0.8 }}
>

    {#if isLoading}

        <div class="full-loader">
            <Loader block size="large" />
        </div>

    {:else}

    {/if}

</div>

<style>

    #post-view {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background-color: var(--accent-lightest);
    }

    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

</style>