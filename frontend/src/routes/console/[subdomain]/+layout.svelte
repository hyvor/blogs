<script lang="ts">
	import { onMount } from "svelte";
	import Nav from "./Nav/Nav.svelte";
    import { page } from '$app/stores';
	import { Loader } from "@hyvor/design/components";
	import consoleApi from "../lib/consoleApi";
	import type { Blog, Language, User } from "../lib/types";
	import { blogStore } from "../lib/stores";
	import { languagesStore } from "../lib/stores/languagesStore";
	import { usersStore } from "../lib/stores/usersStore";

    let isLoading = true;

    interface BlogResponse {
        blog: Blog,
        languages: Language[],
        users: User[]
    }

    onMount(() => {
        
        const subdomain = $page.params.subdomain;

        consoleApi.get<BlogResponse>({
            endpoint: '/blog',
            subdomain,
        }).then(res => {

            blogStore.set(res.blog)
            languagesStore.set(res.languages)
            usersStore.set(res.users)
            
            isLoading = false;
        })
    
    });
</script>

<main>

    {#if isLoading}
        <div class="full-loader">
            <Loader size="large" />
        </div>
    {:else}

        <div id="nav">
            <Nav />
        </div>
        <div id="content">
            <slot />
        </div>

    {/if}
    
</main>

<style>
    main {
        display: flex;
        width: 100%;
        height: 100vh;
    }
    #nav {
        width: 280px;
        padding: 15px;
    }
    #content {
        padding: 15px;
        padding-left: 0;
        flex: 1;
        height: 100%;
    }
    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>