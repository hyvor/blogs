<script lang="ts">
	import {
		ActionList,
		ActionListItem,
		Button,
		Loader,
		Tag,
		Text,
		TextInput,
		toast
	} from '@hyvor/design/components';
	import { createEventDispatcher, onMount } from 'svelte';
	import type { Tag as TagType } from '../../../../../../lib/types';
	import { createTag, getTags, searchTags } from '../../../../../settings/tags/tagActions';
	import { IconPlus } from '@hyvor/icons';
	import TagName from '../../../../../settings/tags/TagName.svelte';

	export let postTags: TagType[] = [];

	let isLoading = true;
	let tags: TagType[] = [];
	let searchedTags: TagType[] = [];
	let search = '';

	$: availableTags = search.trim() !== '' ? searchedTags : tags;

	let searchTimeout: null | ReturnType<typeof setTimeout> = null;

	function handleInput(event: Event) {
		search = (event.target as HTMLInputElement).value;
		isLoading = true;

		if (search.trim() === '') {
			isLoading = false;
			searchedTags = [];
			return;
		}

		if (searchTimeout) {
			clearTimeout(searchTimeout);
		}

		setTimeout(() => {
			searchTags({ search }).then((res) => {
				isLoading = false;
				searchedTags = res;
			});
		}, 250);
	}

	const dispatch = createEventDispatcher<{ select: TagType }>();

	function handleSelect(tag: TagType) {
		dispatch('select', tag);
	}

	function handleCreateIcon() {
		isLoading = true;

		createTag(search.trim())
			.then((res) => {
				handleSelect(res);
			})
			.catch((e) => toast.error(e.message))
			.finally(() => {
				isLoading = false;
			});
	}

	onMount(() => {
		getTags().then((res) => {
			isLoading = false;
			tags = res;
		});
	});
</script>

<div class="search">
	<div class="input">
		<TextInput
			bind:value={search}
			placeholder="Search tags"
			size="small"
			block
			autofocus
			on:input={handleInput}
		/>
	</div>

	<div class="results">
		{#if isLoading}
			<Loader block padding={30} size="small" />
		{:else if availableTags.length}
			<ActionList>
				{#each availableTags as tag (tag.id)}
					<ActionListItem
						on:click={() => handleSelect(tag)}
						disabled={!!postTags.find((t) => t.id === tag.id)}
					>
						<Tag size="small">
							<TagName {tag} small />
						</Tag>
						<Text slot="end" small light>
							{tag.posts_count} post{tag.posts_count === 1 ? '' : 's'}
						</Text>
					</ActionListItem>
				{/each}
			</ActionList>
		{:else}
			<div style="padding:30px;text-align:center">
				<Text small light>No tags</Text>
			</div>
			<div style="text-align:center;padding-bottom:5px;">
				<Button
					size="small"
					variant="outline"
					style="font-weight:normal;font-size:12px;"
					on:click={handleCreateIcon}
				>
					<IconPlus size={15} slot="start" />
					Create tag&nbsp;<b>{search}</b>
				</Button>
			</div>
		{/if}
	</div>
</div>

<style>
	.input :global(.input-wrap) {
		height: 26px !important;
	}

	.results {
		margin-top: 10px;
		max-height: 200px;
		overflow: auto;
	}

	.results :global(.action-list-item.disabled) {
		opacity: 0.5;
		pointer-events: none;
	}
</style>
