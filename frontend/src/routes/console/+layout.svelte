<script lang="ts">
	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import type { AuthUser, BlogList } from "./lib/types";
	import { authUserStore, blogListStore } from "./lib/stores";
	import { Loader } from "@hyvor/design/components";
	import { goto } from "$app/navigation";
    import { page } from '$app/stores';
	import { setConfig, type Config } from "./lib/config";

    interface InitResponse {
        user: AuthUser,
        blogs: BlogList[],
        config: Config,
    }

    let isLoading = true;

    onMount(() => {

        consoleApi.get<InitResponse>({
            endpoint: '/init',
            userApi: true,
        }).then(res => {

            setConfig(res.config);

            authUserStore.set(res.user)
            blogListStore.set(res.blogs)

            isLoading = false;

            if (res.blogs.length > 0) {
                if ($page.url.pathname === '/console') {
                    goto('/console/' + res.blogs[0]!.subdomain)
                }
            } else {
                goto('/console/new')
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