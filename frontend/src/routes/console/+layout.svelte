<script lang="ts">
	import { initTempSubdomain, setTempSubdomain } from './lib/temp';
	import { onMount } from 'svelte';
	import consoleApi from './lib/consoleApi';
	import type { BlogList } from './lib/types';
	import {
		authOrganizationStore,
		authUserStore,
		blogListStore,
		blogSelectorOpenStore,
		resolvedLicenseStore
	} from './lib/stores';
	import { ConsoleLoader, toast } from '@hyvor/design/components';
	import { getConfig, setConfig, type Config } from './lib/config';
	import { isTempStore } from './lib/temp';
	import { page } from '$app/state';
	import { setPreloadedBlog, type BlogResponse } from './(nav)/[subdomain]/blogLoader';
	import {
		CloudContext,
		type CloudContextOrganization,
		type CloudContextUser,
		type ResolvedLicense,
		HyvorBar
	} from '@hyvor/design/cloud';
	import { get } from 'svelte/store';
	import BlogSelectorModal from './lib/components/BlogSelector/BlogSelectorModal.svelte';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	interface InitResponse {
		user: CloudContextUser;
		organization: CloudContextOrganization;
		resolved_license: ResolvedLicense;
		blogs: BlogList[];
		temp_unique_id?: string;
		config: Config;
		preloaded: {
			blog: BlogResponse;
		};
	}

	let isLoading = $state(true);

	function startConsole(switchingOrg = false) {
		isLoading = true;

		const isTemp = page.url.searchParams.has('temp');
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
				authOrganizationStore.set(res.organization);
				resolvedLicenseStore.set(res.resolved_license);
				blogListStore.set(res.blogs);

				if (res.preloaded.blog) {
					setPreloadedBlog(res.preloaded.blog);
				}

				if (res.blogs[0]?.type === 'temp') {
					const subdomain = res.blogs[0].subdomain;
					setTempSubdomain(subdomain);
					if (!tempSubdomain) {
						const event = new CustomEvent('console:temp_blog:created', {
							detail: { subdomain }
						});
						window.dispatchEvent(event);
					}
				}

				if (switchingOrg && !page.url.pathname.startsWith('/console/new')) {
					location.href = '/console';
				}

				isLoading = false;
			})
			.catch((err) => {
				if (err.code === 401) {
					const toPage = page.url.searchParams.has('signup') ? 'signup' : 'login';
					const url = new URL(err.data[toPage + '_url'], location.origin);
					url.searchParams.set('redirect', location.href);
					location.href = url.toString();
				} else {
					toast.error(err.message);
				}
			});
	}

	onMount(startConsole);

	function handleGlobalKeydown(e: KeyboardEvent) {
		const isMac = navigator.platform.toUpperCase().includes('MAC');
		const modifierPressed = isMac ? e.metaKey : e.ctrlKey;

		if (modifierPressed && e.key.toLowerCase() === 'b') {
			e.preventDefault();
			$blogSelectorOpenStore = true;
		}
	}
</script>

<svelte:head>
	<title>Console | Hyvor Blogs</title>
	<meta name="robots" content="noindex" />
</svelte:head>

<svelte:window onkeydown={!isLoading && !$isTempStore ? handleGlobalKeydown : undefined} />

<main>
	{#if isLoading}
		<ConsoleLoader logo="/logo.svg" size={80} />
	{:else}
		<CloudContext
			context={{
				component: 'blogs',
				deployment: getConfig().deployment,
				instance: getConfig().hyvor.instance,
				user: get(authUserStore),
				organization: get(authOrganizationStore),
				license: get(resolvedLicenseStore),
				callbacks: {
					onOrganizationSwitch: (switcher) => {
						isLoading = true;

						switcher
							.then(() => {
								startConsole(true);
							})
							.catch(() => {
								isLoading = false;
							});
					}
				}
			}}
			style="display:flex; flex-direction: column; width: 100%; height: 100vh"
		>
			{#if !$isTempStore}
				<HyvorBar logo="/logo.svg" />
			{/if}

			{@render children?.()}

			{#if !$isTempStore}
				<BlogSelectorModal />
			{/if}
		</CloudContext>
	{/if}
</main>

<style>
	main {
		display: flex;
		flex-direction: column;
		width: 100%;
		height: 100vh;
	}

	@media (max-width: 992px) {
		main {
			display: block;
		}
	}
</style>
