<script lang="ts">
	import { tempUniqueIdStore } from './lib/temp';
	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import type { AuthUser, BlogList } from "./lib/types";
	import { authUserStore, blogListStore } from "./lib/stores";
	import { Loader } from "@hyvor/design/components";
	import { goto } from "$app/navigation";
    import { page } from '$app/stores';
	import { setConfig, type Config } from "./lib/config";
	import { initTempUniqueId, isTempStore } from "./lib/temp";

    interface InitResponse {
        user: AuthUser,
        blogs: BlogList[],
        temp_unique_id?: string,
        config: Config,
    }

    let isLoading = true;

    onMount(() => {

        const isTemp = $page.url.searchParams.has('temp');
        isTempStore.set(isTemp);

        const tempUniqueId = initTempUniqueId();

        consoleApi.get<InitResponse>({
            endpoint: isTemp ? 'init-temp' : 'init',
            userApi: true,
            data: {
                temp_unique_id: isTemp ? tempUniqueId : undefined,
            }
        }).then(res => {
            setConfig(res.config);

            authUserStore.set(res.user)
            blogListStore.set(res.blogs)

            if (res.temp_unique_id) {
                tempUniqueIdStore.set(res.temp_unique_id);
            }

            isLoading = false;
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