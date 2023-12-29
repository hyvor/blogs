<script lang="ts">
	import { onMount } from "svelte";
	import Nav from "./Nav/Nav.svelte";
    import { page } from '$app/stores';
	import { Loader } from "@hyvor/design/components";
	import consoleApi from "../lib/consoleApi";
	import type { Blog, Language, Subscription, User } from "../lib/types";
	import { usersStore } from "../lib/stores/usersStore";
	import { subscriptionStore } from "../lib/stores/subscriptionStore";
	import { blogOriginalStore, blogStore } from "../lib/stores/blogStore";
	import { languagesStore } from "../lib/stores/languagesStore";

    let isLoading = true;

    interface BlogResponse {
        blog: Blog,
        languages: Language[],
        users: User[],
        subscription: Subscription | null
    }

    onMount(() => {
        
        const subdomain = $page.params.subdomain;

        consoleApi.get<BlogResponse>({
            endpoint: '/blog',
            subdomain,
        }).then(res => {

            blogStore.set(res.blog)
            blogOriginalStore.set(res.blog)
            languagesStore.set(res.languages)
            usersStore.set(res.users)
            subscriptionStore.set(res.subscription)
            
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
        min-width: 0;
    }
    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>