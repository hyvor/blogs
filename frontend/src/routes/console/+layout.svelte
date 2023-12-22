<script lang="ts">
	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import type { AuthUser, BlogList } from "./lib/types";
	import { authUserStore, blogListStore } from "./lib/stores";
	import { Loader } from "@hyvor/design/components";
	import { goto } from "$app/navigation";

    interface InitResponse {
        user: AuthUser,
        blogs: BlogList[],
    }

    let isLoading = true;

    onMount(() => {

        consoleApi.get<InitResponse>({
            endpoint: '/init'
        }).then(res => {

            authUserStore.set(res.user)
            blogListStore.set(res.blogs)

            isLoading = false;

            if (res.blogs.length === 0) {
                goto('/console/new')
            } else {
                goto('/console/' + res.blogs[0].subdomain)
            }

        })

    })

</script>

<main>

    {#if isLoading}
        <div class="full-loader">
            <Loader size="large" />
        </div>
    {:else}
        <slot />
    {/if}
    
</main>

<style>
    main {
        display: flex;
        width: 100%;
        height: 100vh;
    }
    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>