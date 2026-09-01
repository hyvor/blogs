<script lang="ts">
	import { Loader, toast } from '@hyvor/design/components';
	import { blogStore } from '../../lib/stores/blogStore';
	import { consoleUrlWithBlog } from '../../lib/consoleUrl';
	import BlogBannedStatus from './@components/BlogStatus/BlogBannedStatus.svelte';
	import { loadBlog } from './blogLoader';
	import { page } from '$app/state';
	import LicenseExpiredNotice from './@components/BlogStatus/LicenseExpiredNotice.svelte';
	import { blogListStore, resolvedLicenseStore } from '../../lib/stores';
	import { getI18n } from '../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let isLoading = $state(true);
	let subdomain = $derived(String(page.params.subdomain));

	$effect(() => {
		if (subdomain) {
			handleBlogChange();
		}
	});

	function handleBlogChange() {
		const userBlogs = $blogListStore.find((b) => b.subdomain === subdomain);

		if (!userBlogs) {
			location.href = '/console';
			return;
		}

		isLoading = true;

		loadBlog(subdomain!)
			.then(() => {
				isLoading = false;
			})
			.catch(() => {
				toast.error(i18n.t('console.failedToLoadBlog'));
			});
	}

	let forcedShow = $derived.by(() => {
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
{:else if $resolvedLicenseStore?.license === null && !forcedShow}
	<LicenseExpiredNotice />
{:else if $blogStore.is_blocked && !forcedShow}
	<BlogBannedStatus />
{:else}
	{@render children?.()}
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
