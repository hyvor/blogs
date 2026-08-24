<script lang="ts">
	import {
		ActionListItem,
		IconMessage,
		Loader,
		Tag as TagComponent,
		Text,
		TextInput
	} from '@hyvor/design/components';
	import type { Tag } from '../../../../../lib/types';
	import { createEventDispatcher, onMount } from 'svelte';
	import { postListFiltersStore } from '../../postListStore';
	import { getTags, searchTags } from '../../../settings/tags/tagActions';
	import TagName from '../../../settings/tags/TagName.svelte';

	let isLoading = $state(true);
	let tags: Tag[] = $state([]);
	let search = $state('');
	let input: HTMLInputElement;
	let err = $state(false);

	function loadTags() {
		isLoading = true;
		tags = [];

		const promise = search.trim() ? searchTags({ search }) : getTags();

		promise
			.then((res) => {
				tags = res;
				isLoading = false;
			})
			.catch((_) => (err = true))
			.finally(() => (isLoading = false));
	}

	const dispatch = createEventDispatcher<{
		select: Tag;
	}>();

	function handleSelect(tag: Tag) {
		dispatch('select', tag);
	}

	let timeout: null | ReturnType<typeof setTimeout> = null;

	function handleSearchInput() {
		if (timeout) clearTimeout(timeout);

		timeout = setTimeout(() => {
			loadTags();
		}, 500);
	}

	onMount(() => {
		loadTags();

		if (input) {
			input.focus();
		}

		return () => {
			if (timeout) clearTimeout(timeout);
		};
	});
</script>

<TextInput
	block
	placeholder="Search tag..."
	autofocus
	bind:value={search}
	bind:input
	on:input={handleSearchInput}
/>

<div class="results">
	{#if isLoading}
		<Loader block padding={35} size="small" />
	{:else if err}
		<IconMessage error padding={35} />
	{:else if tags.length === 0}
		<IconMessage empty padding={35} message="No tags found" iconSize={30} />
	{:else}
		{#each tags as tag (tag.id)}
			<ActionListItem
				on:click={() => handleSelect(tag)}
				selected={$postListFiltersStore.tag?.id === tag.id}
			>
				<TagComponent size="small">
					<TagName {tag} small />
				</TagComponent>
				{#snippet end()}
					<Text light small>
						{tag.posts_count} post{tag.posts_count === 1 ? '' : 's'}
					</Text>
				{/snippet}
			</ActionListItem>
		{/each}
	{/if}
</div>

<style>
	.results {
		margin-top: 10px;
		max-height: 350px;
		overflow: auto;
	}
</style>
