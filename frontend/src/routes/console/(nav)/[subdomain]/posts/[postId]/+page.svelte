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
	import { fade } from 'svelte/transition';

	let isLoading = $state(true);

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

	$effect(() => {
		const postId = page.params.postId;
		if (!postId) return;

		activeRequest?.abort();
		activeRequest = new AbortController();
		isLoading = true;

		consoleApi
			.get<Post>({
				endpoint: '/post/' + postId,
				signal: activeRequest.signal
			})
			.then((res) => {
				setPostAndPostOriginalStore(res);
				if (postView) {
					initPostEditingState(postView, getInitialLanguageId());
				}
				initEditorEventHandlers();

				linkAnalysisLoaderUnsubscriber?.();
				linkAnalysisLoaderUnsubscriber = initLinkAnalysisLoader();

				isLoading = false;
			})
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

<div
	id="post-view"
	class:is-temp={$isTempStore}
	bind:this={postView}
>
	{#if isLoading}
		<div class="full-loader">
			<Loader block size="large" />
		</div>
	{:else}
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
	{/if}
</div>

<style lang="scss">
	#post-view {
		background-color: var(--background);
		padding: 10px 0;
		display: flex;
		flex-direction: column;
		height: 100vh;
		overflow: hidden;
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
		width: 1200px;
		margin: 0 auto 15px;
	}

	.post-inner {
		width: 1200px;
		flex: 1;
		margin: auto;
		display: flex;
		align-items: flex-start;
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
