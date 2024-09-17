<script lang="ts">
	import {
		Avatar,
		Dropdown,
		IconButton,
		Loader,
		SplitControl,
		Tag,
		Text
	} from '@hyvor/design/components';
	import {
		postOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../../postStore';
	import type { Tag as TagType } from '../../../../../../lib/types';
	import { getPrimaryLanguage } from '../../../../../../lib/stores/languagesStore';
	import { IconPlus, IconX } from '@hyvor/icons';
	import TagsSearch from './TagsSearch.svelte';
	import { hasIdArrayChanged } from '../settingsHelpers';
	import UnsavedTag from '../UnsavedTag.svelte';
	import OnlyPrimaryVariant from '../OnlyPrimaryVariant.svelte';
	import { updatePostTags } from '../../../../postActions';
	import TagName from '../../../../../settings/tags/TagName.svelte';

	let dropdownOpen = false;
	let loaderState: 'none' | 'loading' | 'success' | 'error' = 'none';

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

	$: hasChanged = hasIdArrayChanged($postStore.tags, $postOriginalStore.tags);
</script>

<OnlyPrimaryVariant>
	<SplitControl>
		<span slot="label">
			Tags

			<UnsavedTag show={hasChanged} {loaderState} />
		</span>

		<div class="tags">
			<div class="left">
				{#if $postStore.tags.length}
					{#each $postStore.tags as tag}
						<Tag size="small" bg="#f1f1f1">
							<TagName {tag} small />

							<IconButton
								color="red"
								variant="invisible"
								on:click={() => handleRemoveTag(tag.id)}
								size={14}
								slot="end"
							>
								<IconX size={10} />
							</IconButton>
						</Tag>
					{/each}
				{:else}
					<Text light small>No tags</Text>
				{/if}
			</div>

			<div class="right">
				<Dropdown position="bottom" align="end" width={300} bind:show={dropdownOpen}>
					<IconButton color="input" size={22} slot="trigger">
						<IconPlus size={14} />
					</IconButton>

					<TagsSearch slot="content" postTags={$postStore.tags} on:select={handleAddTag} />
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
