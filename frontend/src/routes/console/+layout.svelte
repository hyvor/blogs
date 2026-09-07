<script lang="ts">
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
	import {
		ConsoleLoader,
		IconMessage,
		InternationalizationProvider,
		toast
	} from '@hyvor/design/components';
	import { CONSOLE_LANGUAGES } from './lib/i18n';
	import { getConfig, setConfig, type Config } from './lib/config';
	import { page } from '$app/state';
	import { setPreloadedBlog, type BlogResponse } from './(nav)/[subdomain]/blogLoader';
	import { setPreloadedPost } from './(nav)/[subdomain]/posts/[postId]/postLoader';
	import type { Post } from './lib/types';
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
		resolved_license: ResolvedLicense | null;
		blogs: BlogList[];
		temp_unique_id?: string;
		config: Config;
		preloaded: {
			blog: BlogResponse;
			post: Post | null;
		};
	}

	let isLoading = $state(true);
	let error = $state('');

	const isPostPage = $derived(page.url.pathname.match(/\/console\/[^\/]+\/posts\/[^\/]+/) != null);

	function getBlogHint() {
		const match = page.url.pathname.match(/^\/console\/([^\/]+)/);
		return match ? match[1] : undefined;
	}

	function getPostHint() {
		const match = page.url.pathname.match(/^\/console\/[^\/]+\/posts\/([^\/]+)/);
		return match ? match[1] : undefined;
	}

	function startConsole(switchingOrg = false) {
		isLoading = true;
		error = '';

		consoleApi
			.get<InitResponse>({
				endpoint: 'init',
				userApi: true,
				data: {
					blog_hint: getBlogHint(),
					post_hint: getPostHint()
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

				if (res.preloaded.post) {
					setPreloadedPost(res.preloaded.post);
				}

				if (switchingOrg && !page.url.pathname.startsWith('/console/new')) {
					location.href = '/console';
				}

				isLoading = false;
			})
			.catch((err) => {
				if (typeof err === 'object' && err.code === 401) {
					const toPage = page.url.searchParams.has('signup') ? 'signup' : 'login';
					const url = new URL(err.data[toPage + '_url'], location.origin);
					url.searchParams.set('redirect', location.href);
					location.href = url.toString();
				} else {
					error = 'We were unable to initialize the console. Please try again.';
				}
				isLoading = false;
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

<svelte:window onkeydown={!isLoading ? handleGlobalKeydown : undefined} />

<InternationalizationProvider languages={CONSOLE_LANGUAGES}>
	<main>
		{#if isLoading}
			<ConsoleLoader logo="/logo.svg" size={80} />
		{:else if error}
			<IconMessage
				error
				message={error}
				cta={{
					text: 'Retry',
					onClick: () => {
						startConsole();
					}
				}}
			/>
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
				{#if !isPostPage}
					<HyvorBar logo="/logo.svg" />
				{/if}

				{@render children?.()}

				<BlogSelectorModal />
			</CloudContext>
		{/if}
	</main>
</InternationalizationProvider>

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
