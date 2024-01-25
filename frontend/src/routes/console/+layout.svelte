<script lang="ts">
	import { initTempSubdomain, setTempSubdomain } from './lib/temp';
	import { onMount } from "svelte";
	import consoleApi from "./lib/consoleApi";
	import type { AuthUser, BlogList } from "./lib/types";
	import { authUserStore, blogListStore } from "./lib/stores";
	import { Loader, toast } from "@hyvor/design/components";
	import { goto } from "$app/navigation";
    import { page } from '$app/stores';
	import { setConfig, type Config } from "./lib/config";
	import { isTempStore } from "./lib/temp";
	import { APP_URL } from "../../lib";

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

        const tempSubdomain = initTempSubdomain();

        consoleApi.get<InitResponse>({
            endpoint: isTemp ? 'init-temp' : 'init',
            userApi: true,
            data: {
                temp_subdomain: isTemp ? tempSubdomain : undefined,
            }
        }).then(res => {
            setConfig(res.config);

            authUserStore.set(res.user)
            blogListStore.set(res.blogs)

            if (res.blogs[0]?.type === 'temp') {
                setTempSubdomain(res.blogs[0].subdomain);
            }

            isLoading = false;
        }).catch(err => {

            if (err.code === 401) {
                const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
                location.href = APP_URL + `/api/auth/${toPage}?redirect=` + encodeURIComponent(location.href);
            } else {
                toast.error(err.message);
            }

        })

    })

</script>

<main>

    {#if isLoading}
        <div class="full-loader">
            <Loader size="large">
                <div>
                    {#if $isTempStore}
                        Creating your temporary blog...
                    {/if}
                </div>
            </Loader>
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