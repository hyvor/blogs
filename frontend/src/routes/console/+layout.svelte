<script lang="ts">
	import { initTempSubdomain, setTempSubdomain } from './lib/temp';
	import { onMount } from 'svelte';
	import consoleApi from './lib/consoleApi';
	import type { AuthUser, BlogList } from './lib/types';
	import { authUserStore, blogListStore } from './lib/stores';
	import { Loader, toast, HyvorBar } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { getConfig, setConfig, type Config } from './lib/config';
	import { isTempStore } from './lib/temp';
	import {loadBlog} from "./(nav)/[subdomain]/blogLoader";
	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		user: AuthUser;
		blogs: BlogList[];
		temp_unique_id?: string;
		config: Config;
	}

	let isLoading = $state(true);

	onMount(() => {
		const isTemp = $page.url.searchParams.has('temp');
		isTempStore.set(isTemp);

		const tempSubdomain = initTempSubdomain();

		consoleApi
			.get<InitResponse>({
				endpoint: isTemp ? 'init-temp' : 'init',
				userApi: true,
				data: {
					temp_subdomain: isTemp ? tempSubdomain : undefined
				}
			})
			.then((res) => {
				setConfig(res.config);

				authUserStore.set(res.user);
				blogListStore.set(res.blogs);

				if (res.blogs[0]?.type === 'temp') {
					const subdomain = res.blogs[0].subdomain;
					setTempSubdomain(subdomain);
					if (!tempSubdomain) {
						const event = new CustomEvent('console:temp_blog:created', { detail: { subdomain } });
						window.dispatchEvent(event);
					}
				}

				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = $page.url.searchParams.has('signup') ? 'signup' : 'login';
					location.href = `/api/auth/${toPage}?redirect=` + encodeURIComponent(location.href);
				} else {
					toast.error(err.message);
				}
			});
	});
</script>

<svelte:head>
	<title>Console · Hyvor Blogs</title>
	<meta name="robots" content="noindex" />
</svelte:head>

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
		<HyvorBar
			instance={getConfig().hyvor.instance}
			product="blogs"
			config={{
				twitter: 'https://twitter.com/HyvorBlogs',
				g2: 'https://www.g2.com/products/hyvor-blogs/reviews'
			}}
		/>
		{@render children?.()}
	{/if}
</main>

<style>
	main {
		display: flex;
		flex-direction: column;
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

	@media (max-width: 992px) {
		main {
			display: block;
		}
	}
</style>
