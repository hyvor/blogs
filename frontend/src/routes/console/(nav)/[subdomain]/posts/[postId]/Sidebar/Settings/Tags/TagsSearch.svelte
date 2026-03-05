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
	import type { Tag as TagType } from '../../../../../../../lib/types';
	import { createTag, getTags, searchTags } from '../../../../../settings/tags/tagActions';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconLock from '@hyvor/icons/IconLock';

	import TagName from '../../../../../settings/tags/TagName.svelte';

	interface Props {
		selectedTags?: TagType[];
		createPrivate?: boolean;
	}

	let { selectedTags = [], createPrivate = false }: Props = $props();

	let isLoading = $state(true);
	let tags: TagType[] = $state([]);
	let searchedTags: TagType[] = $state([]);
	let search = $state('');

	let availableTags = $derived(search.trim() !== '' ? searchedTags : tags);

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

	function handleCreateTag() {
		isLoading = true;

		createTag(search.trim(), createPrivate)
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
		{:else}
			<ActionList>
				{#each availableTags as tag (tag.id)}
					<ActionListItem
						on:click={() => handleSelect(tag)}
						disabled={!!selectedTags.find((t) => t.id === tag.id)}
					>
						<Tag size="small">
							<TagName {tag} small />
						</Tag>
						{#snippet end()}
							<Text small light>
								{tag.posts_count} post{tag.posts_count === 1 ? '' : 's'}
							</Text>
						{/snippet}
					</ActionListItem>
				{/each}

				{#if search !== '' && availableTags.some((t) => t.variants[0]?.name === search) === false}
					<ActionListItem>
						<Button
							size="small"
							variant="outline"
							style="font-weight:normal;font-size:12px;"
							on:click={handleCreateTag}
						>
							{#snippet start()}
								<IconPlus size={15} />
							{/snippet}
							Create tag&nbsp;<b>{search}</b>
							{#if createPrivate}
								<IconLock size={12} style="margin-left:4px;" />
							{/if}
						</Button>
					</ActionListItem>
				{/if}
			</ActionList>
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
