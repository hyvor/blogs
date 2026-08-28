<script lang="ts">
	import { IconMessage, Loader } from '@hyvor/design/components';
	import {
		documentStore,
		postOriginalStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../postStore';
	import PostBody from '../Body/PostBody.svelte';
	import TopBar from '../TopBar/TopBar.svelte';
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import { getDocumentForPost } from '../documentActions';
	import { seoService } from '../../seoStore';
	import { linksService } from '../Sidebar/Links/linksStore';

	interface Props {
		postId: number;
		langCode: string;
	}

	let { postId, langCode }: Props = $props();

	let isLoading = $state(true);
	let error = $state('');
	let postView: HTMLDivElement;

	onMount(() => {
		// const preloadedPost = getPreloadedPost(postId);
		// if (preloadedPost) {
		// 	handlePost(preloadedPost, null);
		// 	return;
		// }

		isLoading = true;

		getDocumentForPost(Number(postId), langCode)
			.then(({ post, variant, document }) => {
				postOriginalStore.set({ ...post });
				postStore.set({ ...post });

				postVariantOriginalStore.set({ ...variant });
				postVariantStore.set({ ...variant });

				documentStore.set(document);

				seoService.start(document.checkpoint_content);
				linksService.start(document.checkpoint_content);

				isLoading = false;
			})
			.catch((e) => {
				error = e.message || 'Failed to load post';
				isLoading = false;
			});

		return () => {
			seoService.stop();
			linksService.stop();
		};
	});
</script>

<div id="post-view" bind:this={postView}>
	{#if isLoading}
		<div class="full-loader">
			<Loader block size="large" colorTrack="transparent">Loading post...</Loader>
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
			<TopBar />
			<PostBody />
		</div>
	{/if}
</div>

<style>
	#post-view {
		background-color: white;
		height: 100vh;
		width: 100%;
		overflow: auto;
		--text-faded: #343434;
	}

	.container {
		min-height: 100vh;
		display: flex;
		flex-direction: column;
	}

	.full-loader {
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		flex: 1;
	}
</style>
