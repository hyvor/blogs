<script lang="ts">
	import { onMount } from 'svelte';
	import { Loader, toast } from '@hyvor/design/components';
	import { blogStore } from '../../lib/stores/blogStore';
	import TempBlogNotice from './Temp/TempBlogNotice.svelte';
	import { consoleUrlWithBlog } from '../../lib/consoleUrl';
	import BlogBannedStatus from './@components/BlogStatus/BlogBannedStatus.svelte';
	import { loadBlog } from './blogLoader';
	import { page } from '$app/state';
	import LicenseExpiredNotice from './@components/BlogStatus/LicenseExpiredNotice.svelte';
	import { isTempStore } from '../../lib/temp';
	import { blogListStore, resolvedLicenseStore } from '../../lib/stores';
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let isLoading = $state(true);
	let subdomain = $derived(String(page.params.subdomain));

	onMount(() => {
		const userBlogs = $blogListStore.find((b) => b.subdomain === subdomain);

		if (!userBlogs) {
			location.href = '/console';
			return;
		}

		loadBlog(subdomain!)
			.then(() => {
				isLoading = false;
			})
			.catch(() => {
				toast.error('Unable to load blog');
			});
	});

	let forcedShow = $derived.by(() => {
		if ($isTempStore) {
			return true;
		}

		if (page.url.pathname === consoleUrlWithBlog('billing')) {
			return true;
		}

		if (page.url.pathname.startsWith(consoleUrlWithBlog('settings'))) {
			return true;
		}

		return false;
	});
</script>

<svelte:head>
	<title>
		{$blogStore ? $blogStore.subdomain : 'Loading...'} · Console · Hyvor Blogs
	</title>
</svelte:head>

{#if isLoading}
	<div class="full-loader">
		<Loader size="large" />
	</div>
{:else}
	<TempBlogNotice />

	{#if $resolvedLicenseStore.license === null && !forcedShow}
		<LicenseExpiredNotice />
	{:else if $blogStore.is_blocked && !forcedShow}
		<BlogBannedStatus />
	{:else}
		{@render children?.()}
	{/if}
{/if}

<style>
	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
</style>
