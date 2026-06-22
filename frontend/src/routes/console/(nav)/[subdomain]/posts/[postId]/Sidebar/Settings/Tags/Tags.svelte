<script lang="ts">
	import { Dropdown, IconButton, SplitControl, Text } from '@hyvor/design/components';
	import {
		postOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../../postStore';
	import type { Tag as TagType } from '../../../../../../../lib/types';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconX from '@hyvor/icons/IconX';

	import TagsSearch from './TagsSearch.svelte';
	import { hasIdArrayChanged } from '../settingsHelpers';
	import UnsavedTag from '../UnsavedTag.svelte';
	import OnlyPrimaryVariant from '../OnlyPrimaryVariant.svelte';
	import { updatePostTags } from '../../../../postActions';
	import TagChip from '../../../../TagChip.svelte';

	let dropdownOpen = $state(false);
	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function saveTags() {
		loaderState = 'loading';

		updatePostTags($postStore.tags)
			.then(() => (loaderState = 'success'))
			.catch(() => (loaderState = 'error'));
	}

	function handleRemoveTag(tagId: number) {
		updatePostStore({
			tags: $postStore.tags.filter((a) => a.id !== tagId)
		});

		if ($postVariantStore.status !== 'published') saveTags();
	}

	function handleAddTag(e: CustomEvent<TagType>) {
		dropdownOpen = false;

		const tag = e.detail;
		if ($postStore.tags.find((a) => a.id === tag.id)) return;

		updatePostStore({
			tags: [...$postStore.tags, tag]
		});

		if ($postVariantStore.status !== 'published') saveTags();
	}

	let hasChanged = $derived(hasIdArrayChanged($postStore.tags, $postOriginalStore.tags));
</script>

<OnlyPrimaryVariant>
	<SplitControl>
		{#snippet label()}
			<span>
				Tags

				<UnsavedTag show={hasChanged} {loaderState} />
			</span>
		{/snippet}

		<div class="tags">
			<div class="left">
				{#if $postStore.tags.length}
					{#each $postStore.tags as tag}
						<TagChip {tag}>
							{#snippet end()}
								<IconButton
									color="red"
									variant="invisible"
									on:click={() => handleRemoveTag(tag.id)}
									size={14}
								>
									<IconX size={10} />
								</IconButton>
							{/snippet}
						</TagChip>
					{/each}
				{:else}
					<Text light small>No tags</Text>
				{/if}
			</div>

			<div class="right">
				<Dropdown position="bottom" align="end" width={300} bind:show={dropdownOpen}>
					{#snippet trigger()}
						<IconButton color="input" size={22}>
							<IconPlus size={14} />
						</IconButton>
					{/snippet}

					{#snippet content()}
						<TagsSearch selectedTags={$postStore.tags} on:select={handleAddTag} />
					{/snippet}
				</Dropdown>
			</div>
		</div>
	</SplitControl>
</OnlyPrimaryVariant>

<style>
	.tags {
		display: flex;
	}

	.left {
		flex: 1;
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 5px;
	}
</style>
