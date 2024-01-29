<script lang="ts">
	import { onMount } from "svelte";
	import Nav from "./Nav/Nav.svelte";
    import { page } from '$app/stores';
	import { Loader, toast } from "@hyvor/design/components";
	import consoleApi from "../lib/consoleApi";
	import type { Blog, BlogCounts, Language, Subscription, UsageTypes, User } from "../lib/types";
	import { usersStore } from "../lib/stores/usersStore";
	import { subscriptionStore, usageStore } from "../lib/stores/subscriptionStore";
	import { blogCountsStore, blogOriginalStore, blogStore } from "../lib/stores/blogStore";
	import { languagesStore } from "../lib/stores/languagesStore";
	import { goto } from "$app/navigation";
	import TempBlogNotice from "./Temp/TempBlogNotice.svelte";
	import { isTempStore } from "../lib/temp";

    let isLoading = true;

    interface BlogResponse {
        blog: Blog,
        languages: Language[],
        users: User[],
        subscription: Subscription | null,
        usage: UsageTypes,
        counts: BlogCounts
    }

    onMount(() => {
        
        const subdomain = $page.params.subdomain;

        consoleApi.get<BlogResponse>({
            endpoint: '/blog',
            subdomain,
        }).then(res => {

            if (res.blog.type === 'temp' && !$isTempStore) {
                location.href = '/console'
            }

            blogStore.set(res.blog)
            blogOriginalStore.set(res.blog)
            blogCountsStore.set(res.counts)
            languagesStore.set(res.languages)
            usersStore.set(res.users)
            subscriptionStore.set(res.subscription)
            usageStore.set(res.usage);
            
            isLoading = false;
        }).catch(() => {
            toast.error('Unable to load blog');
        })
    
    });
</script>

<svelte:head>
    <title>
        {$blogStore ? $blogStore.subdomain : 'Loading...'} · Console · Hyvor Blogs
    </title>
</svelte:head>

<main id="blog-main">

    {#if isLoading}
        <div class="full-loader">
            <Loader size="large" />
        </div>
    {:else}

        <TempBlogNotice />

        <div id="nav">
            <Nav />
        </div>
        <div id="content">
            <slot />
        </div>

    {/if}
    
</main>

<style>
    main#blog-main {
        display: flex;
        width: 100%;
        height: calc(100vh - var(--top-offset, 0));
    }
    #nav {
        width: 280px;
        padding: 15px;
        padding-right: 0;
    }
    #content {
        padding: 15px;
        flex: 1;
        height: 100%;
        min-width: 0;
        overflow: auto;
    }
    .full-loader {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
</style>