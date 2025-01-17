<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { Loader, toast } from '@hyvor/design/components';
	import {
		hasTrialEndedAndNotSubscribed,
	} from '../../lib/stores/subscriptionStore';
	import { blogStore } from '../../lib/stores/blogStore';
	import TempBlogNotice from './Temp/TempBlogNotice.svelte';
	import TrialEndedStatus from './@components/BlogStatus/TrialEndedStatus.svelte';
	import { consoleUrlWithBlog } from '../../lib/consoleUrl';
	import BlogBannedStatus from './@components/BlogStatus/BlogBannedStatus.svelte';
	import {loadBlog} from "./blogLoader";

	let isLoading = true;

	onMount(() => {
		const subdomain = $page.params.subdomain;

		loadBlog(subdomain)
			.then(() => {
				isLoading = false;
			})
			.catch(() => {
				toast.error('Unable to load blog');
			});
	});

	$: forcedShow =
		!isLoading &&
		($page.url.pathname === consoleUrlWithBlog('billing') ||
			$page.url.pathname.startsWith(consoleUrlWithBlog('settings')));
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

	{#if hasTrialEndedAndNotSubscribed() && !forcedShow}
		<TrialEndedStatus />
	{:else if $blogStore.is_blocked && !forcedShow}
		<BlogBannedStatus />
	{:else}
		<slot />
	{/if}
{/if}

<style>
	main#blog-main {
		display: flex;
		width: 100%;
		height: calc(100vh - var(--top-offset, 0));
		flex: 1;
		min-height: 0;
	}
	#nav {
		width: 280px;
		padding: 15px;
		padding-right: 0;
	}
	.content {
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
