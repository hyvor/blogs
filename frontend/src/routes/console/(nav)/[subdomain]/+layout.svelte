<script lang="ts">
	import { onMount } from 'svelte';
	import { Loader, toast } from '@hyvor/design/components';
	import { blogStore, licenseStore } from '../../lib/stores/blogStore';
	import TempBlogNotice from './Temp/TempBlogNotice.svelte';
	import { consoleUrlWithBlog } from '../../lib/consoleUrl';
	import BlogBannedStatus from './@components/BlogStatus/BlogBannedStatus.svelte';
	import { loadBlog } from './blogLoader';
	import { page } from '$app/state';
	import LicenseExpiredNotice from './@components/BlogStatus/LicenseExpiredNotice.svelte';
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let isLoading = $state(true);

	onMount(() => {
		const subdomain = page.params.subdomain;

		loadBlog(subdomain!)
			.then(() => {
				isLoading = false;
			})
			.catch(() => {
				toast.error('Unable to load blog');
			});
	});

	let forcedShow = $derived(
		!isLoading &&
			(page.url.pathname === consoleUrlWithBlog('billing') ||
				page.url.pathname.startsWith(consoleUrlWithBlog('settings')))
	);
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

	{#if $licenseStore == null && !forcedShow}
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

	@media (max-width: 992px) {
		main#blog-main {
			flex-direction: column;
			height: initial;
		}
		#nav {
			margin-top: 15px;
			padding: 0;
			width: 100%;
		}
		#content {
			padding: 15px 0;
		}
	}
</style>
