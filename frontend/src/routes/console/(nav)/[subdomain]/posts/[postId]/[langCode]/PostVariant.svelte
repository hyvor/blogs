<script lang="ts">
	import { IconMessage, Loader } from '@hyvor/design/components';
	import type { Post, PostVariant } from '../../../../../lib/types';
	import {
		postOriginalStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../postStore';
	import PostBody from '../Body/PostBody.svelte';
	import PostSidebar from '../Sidebar/PostSidebar.svelte';
	import TopBar from '../TopBar/TopBar.svelte';
	import { isTempStore } from '../../../../../lib/temp';
	import type { Unsubscriber } from 'svelte/store';
	import { onMount } from 'svelte';
	import { getPost } from '../../postActions';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';

	interface Props {
		postId: number;
		langCode: string;
	}

	let { postId, langCode }: Props = $props();

	let isLoading = $state(true);
	let error = $state('');

	let postView: HTMLDivElement;
	let linkAnalysisLoaderUnsubscriber: Unsubscriber | null = null;

	function completePostLoading(post: Post, variant: PostVariant | null) {
		postOriginalStore.set({ ...post });
		postStore.set({ ...post });

		postVariantOriginalStore.set(variant ? { ...variant } : null);
		postVariantStore.set(variant ? { ...variant } : null);

		isLoading = false;
	}

	onMount(() => {
		// const preloadedPost = getPreloadedPost(postId);
		// if (preloadedPost) {
		// 	handlePost(preloadedPost, null);
		// 	return;
		// }

		isLoading = true;

		getPost(Number(postId), langCode)
			.then(({ post, variant }) => {
				completePostLoading(post, variant);
			})
			.catch((e) => {
				error = e.message || 'Failed to load post';
				isLoading = false;
			});

		return () => {
			linkAnalysisLoaderUnsubscriber?.();
			linkAnalysisLoaderUnsubscriber = null;
		};
	});
</script>

<div id="post-view" class:is-temp={$isTempStore} bind:this={postView}>
	{#if isLoading}
		<div class="full-loader">
			<Loader block size="large" />
		</div>
	{:else if error}
		<IconMessage
			message={error}
			error
			cta={{
				text: 'Go to Posts',
				onClick: () => goto(consoleUrlWithBlog('/posts'))
			}}
		/>
	{:else}
		<div class="container">
			<div class="top-bar-wrap">
				<TopBar />
			</div>

			<div class="post-inner">
				<div class="post-left">
					<PostBody />
				</div>

				<div class="post-right">
					<PostSidebar />
				</div>
			</div>
		</div>
	{/if}
</div>

<style lang="scss">
	#post-view {
		background-color: var(--background);
		height: 100vh;
		overflow: auto;
	}

	.container {
		min-height: 100vh;
		display: flex;
		flex-direction: column;
		padding: 0 15px;
	}

	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 1;
	}

	.top-bar-wrap {
		margin-bottom: 15px;
		position: sticky;
		top: 0;
		z-index: 100;
		height: 40px;
	}

	.post-inner {
		flex: 1;
		margin: auto;
		display: flex;
		align-items: flex-start;
		min-height: calc(100vh - 70px);
		width: 100%;
	}

	.post-left {
		position: relative;
		flex: 1;
	}

	.post-right {
		margin-left: 15px;
		height: calc(100vh - 70px);
		display: flex;
		flex-direction: column;
		min-width: 0;
		position: sticky;
		top: 55px;
	}

	@media (max-width: 992px) {
		.top-bar-wrap {
			width: 100%;
			padding: 0 15px;
		}
		.post-inner {
			width: 100%;
			padding: 0 15px;
			flex-direction: column;
			margin-top: 15px;
		}
		.post-right {
			margin-left: 0;
			width: 100%;
		}
		.post-left {
			width: 100%;
		}
	}
</style>
