<script lang="ts">
	import { page } from '$app/state';
	import { IconButton, Loader } from '@hyvor/design/components';
	import { scale } from 'svelte/transition';
	import consoleApi from '../../../../lib/consoleApi';
	import type { Post } from '../../../../lib/types';
	import {
		initPostEditingState,
		postEditingStatusStore,
		postStore,
		setPostAndPostOriginalStore,
		updatePostEditingStatusValue
	} from '../postStore';
	import PostBody from './Body/PostBody.svelte';
	import PostSidebar from './Sidebar/PostSidebar.svelte';
	import { blogStore } from '../../../../lib/stores/blogStore';
	import IconCaretLeftFill from '@hyvor/icons/IconCaretLeftFill';
	import { initEditorEventHandlers } from './Body/Editor/editorEvents';
	import { isTempStore } from '../../../../lib/temp';
	import { consoleUrlWithBlog } from '../../../../lib/consoleUrl';
	import type { Unsubscriber } from 'svelte/store';
	import { initLinkAnalysisLoader } from './Sidebar/Links/linkLoader';
	import { languagesStore } from '../../../../lib/stores/languagesStore';

	let isLoading = $state(true);

	let postView: HTMLDivElement | undefined = $state();
	let linkAnalysisLoaderUnsubscriber: Unsubscriber | null = null;
	let activeRequest: AbortController | null = null;

	function getLangIdFromUrl() {
		if ($languagesStore.length === 0) return null;

		const languageCode = page.url.searchParams.get('lang')?.trim().toLowerCase();
		if (!languageCode) return null;

		const selectedLanguage = $languagesStore.find(
			(language) => language.code.toLowerCase() === languageCode
		);
		if (!selectedLanguage) return null;

        console.log(selectedLanguage);

        return selectedLanguage.id;
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
					initPostEditingState(postView, getLangIdFromUrl());
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

	function getBackUrl() {
		const postData = $postStore;
		return consoleUrlWithBlog(postData && postData.is_page ? '/pages' : '/posts');
	}
</script>

<div
	id="post-view"
	class:is-temp={$isTempStore}
	in:scale={{ duration: 300, opacity: 0, start: 0.8 }}
	bind:this={postView}
>
	<div class="back">
		<a href={getBackUrl()}>
			<IconButton variant="invisible" color="gray">
				<IconCaretLeftFill />
			</IconButton>
		</a>
	</div>

	{#if isLoading}
		<div class="full-loader">
			<Loader block size="large" />
		</div>
	{:else}
		<div class="post-inner">
			<div class="post-left">
				<!-- <PostHeader /> -->
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
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		background-color: var(--accent-lightest);
		overflow: auto;
		padding: 20px 0;
		z-index: 101;
	}

	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.post-inner {
		width: 1200px;
		margin: auto;
		display: flex;
		align-items: flex-start;
	}

	.post-left {
		width: 700px;
		min-height: calc(100vh - 40px);
		position: relative;
	}

	.post-right {
		flex: 1;
		margin-left: 15px;
		height: calc(100vh - 40px);
		display: flex;
		flex-direction: column;
		position: sticky;
		top: 0px;
		z-index: 10;
		min-width: 0;
	}

	.back {
		margin-left: 15px;
		margin-top: 15px;
		position: fixed;
		top: 0;
		left: 0;
		z-index: 100;
		font-weight: 600;
		width: 35px;
		height: 35px;
	}

	#post-view.is-temp {
		height: calc(100% - var(--top-offset));
		top: var(--top-offset);
		.back {
			top: var(--top-offset);
		}
	}

	@media (max-width: 992px) {
		.post-inner {
			width: 100%;
			padding: 0 15px;
			flex-direction: column;
			margin-top: 15px;
		}
		.back {
			position: relative;
			margin-top: 0;
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
