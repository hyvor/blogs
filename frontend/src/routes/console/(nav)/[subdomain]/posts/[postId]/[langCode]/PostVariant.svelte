<script lang="ts">
	import { IconMessage, Loader } from '@hyvor/design/components';
	import {
		documentStore,
		postEditingPublished,
		postOriginalStore,
		postSidebarStore,
		postStore,
		postSuggestionModeStore,
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
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		postId: number;
		langCode: string;
	}

	let { postId, langCode }: Props = $props();

	let isLoading = $state(true);
	let error = $state('');
	let postView: HTMLDivElement;

	onMount(() => {
		postSidebarStore.set(null);
		postEditingPublished.set(false);
		postSuggestionModeStore.set('editing');

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
				error = e.message || i18n.t('console.postEditor.loadFailed');
				isLoading = false;
			});

		return () => {
			seoService.stop();
			linksService.stop();
			postSidebarStore.set(null);
		};
	});
</script>

<div id="post-view" bind:this={postView}>
	{#if isLoading}
		<div class="full-loader">
			<Loader block size="large" colorTrack="transparent"
				>{i18n.t('console.postEditor.loading')}</Loader
			>
		</div>
	{:else if error}
		<IconMessage
			message={error}
			error
			cta={{
				text: i18n.t('console.postEditor.goToPosts'),
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

	/* the richtext suggestions panel is position: fixed with a low z-index; keep it
	   out of the way whenever a modal dialog (publish, update, compare, confirm) is
	   open so it doesn't float over the modal */
	:global(#hds-base:has([aria-modal='true']) .pm-suggestions-panel-wrap) {
		display: none;
	}
</style>
