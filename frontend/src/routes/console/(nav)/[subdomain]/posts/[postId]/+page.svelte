<script lang="ts">
	import { page } from '$app/state';
	import { Loader } from '@hyvor/design/components';
	import consoleApi from '../../../../lib/consoleApi';
	import type { Post } from '../../../../lib/types';
	import { initPostEditingState, setPostAndPostOriginalStore } from '../postStore';
	import PostBody from './Body/PostBody.svelte';
	import PostSidebar from './Sidebar/PostSidebar.svelte';
	import TopBar from './TopBar/TopBar.svelte';
	import { initEditorEventHandlers } from './Body/Editor/editorEvents';
	import { isTempStore } from '../../../../lib/temp';
	import type { Unsubscriber } from 'svelte/store';
	import { initLinkAnalysisLoader } from './Sidebar/Links/linkLoader';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import { getPreloadedPost } from './postLoader';

	let isLoading = $state(false);
	let storesSet = $state(false);

	let postView: HTMLDivElement | undefined = $state();
	let linkAnalysisLoaderUnsubscriber: Unsubscriber | null = null;
	let activeRequest: AbortController | null = null;

	function getInitialLanguageId() {
		const primaryId = $languagesStore.find((language) => language.is_primary)!.id;

		const languageCodeFromUrl = page.url.searchParams.get('lang')?.trim().toLowerCase();
		const languageFromUrl = $languagesStore.find(
			(l) => l.code.toLowerCase() === languageCodeFromUrl
		);

		if (languageFromUrl) return languageFromUrl.id;

		return primaryId;
	}

	function handlePost(post: Post) {
		setPostAndPostOriginalStore(post);
		if (postView) {
			initPostEditingState(postView, getInitialLanguageId());
		}
		initEditorEventHandlers();

		linkAnalysisLoaderUnsubscriber?.();
		linkAnalysisLoaderUnsubscriber = initLinkAnalysisLoader();

		isLoading = false;
		storesSet = true;
	}

	$effect.pre(() => {
		const postId = page.params.postId;
		if (!postId) return;

		activeRequest?.abort();

		const preloadedPost = getPreloadedPost(postId);
		if (preloadedPost) {
			handlePost(preloadedPost);
			return;
		}

		isLoading = true;

		activeRequest = new AbortController();

		consoleApi
			.get<Post>({
				endpoint: '/post/' + postId,
				signal: activeRequest.signal
			})
			.then(handlePost)
			.catch((error) => {
				if (error?.name === 'AbortError') return;
				throw error;
			});

		return () => {
			activeRequest?.abort();
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
	}

	.container {
		height: 100vh;
		width: 1200px;
		display: flex;
		flex-direction: column;
		margin: 0 auto;
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
	}

	.post-inner {
		flex: 1;
		margin: auto;
		display: flex;
		align-items: flex-start;
		min-height: calc(100vh - 76px);
    	width: 100%;
	}

	.post-left {
		width: 700px;
		position: relative;
		height: 100%;
	}

	.post-right {
		flex: 1;
		margin-left: 15px;
		height: 100%;
		display: flex;
		flex-direction: column;
		min-width: 0;
	}

	#post-view.is-temp {
		height: calc(100% - var(--top-offset));
		top: var(--top-offset);
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
