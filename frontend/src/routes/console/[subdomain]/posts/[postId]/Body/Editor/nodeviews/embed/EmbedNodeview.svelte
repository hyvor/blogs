<script lang="ts" generics="T extends 'embed'|'link'">
	import { IconMessage, Loader } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { getUnfold } from '../../../../../../../lib/actions/urlDataActions';
	import EmbedHtmlDisplay from '../../plugins/slash/Embed/EmbedHtmlDisplay.svelte';
	import BookmarkDisplay from '../../plugins/slash/Bookmark/BookmarkDisplay.svelte';

	export let url: string;

	export let type = 'embed' as T;

	let isLoading = true;
	let unfolded: any;
	let error: null | string = null;

	onMount(() => {
		getUnfold(url, type)
			.then((res) => {
				unfolded = res;
			})
			.catch(() => {
				error = 'Failed to load embed';
			})
			.finally(() => {
				isLoading = false;
			});
	});
</script>

<div>
	{#if isLoading}
		<Loader block padding={100} />
	{:else if error}
		<IconMessage error padding={60} message={error} iconSize={70} />
	{:else if type === 'embed'}
		<EmbedHtmlDisplay html={unfolded.embed} />
	{:else}
		<BookmarkDisplay link={unfolded} />
	{/if}
</div>

<style>
</style>
