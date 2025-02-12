<script lang="ts">
	import { SplitControl, Tag, Text } from '@hyvor/design/components';
	import type { Tag as TagType } from '../../../../../../../../lib/types';
	import { postOriginalStore, postStore } from '../../../../../postStore';
	import TagName from '../../../../../../settings/tags/TagName.svelte';

	interface Props {
		diff: boolean;
	}

	let { diff }: Props = $props();

	let allTags: TagType[] = [];

	$postOriginalStore.tags.map((tag) => allTags.push(tag));
	$postStore.tags.map((tag) => (allTags.find((t) => t.id === tag.id) ? null : allTags.push(tag)));

	function tagFoundIn(tag: TagType, in_: TagType[]) {
		return in_.find((t) => t.id === tag.id);
	}

	function getColor(tag: TagType) {
		if (tagFoundIn(tag, $postOriginalStore.tags) && tagFoundIn(tag, $postStore.tags)) {
			return 'default';
		}

		if (tagFoundIn(tag, $postOriginalStore.tags)) {
			return 'red';
		}

		if (tagFoundIn(tag, $postStore.tags)) {
			return 'green';
		}

		return 'default';
	}
</script>

<SplitControl label="Tags">
	<div class="wrap">
		{#if diff}
			{#each allTags as tag (tag.id)}
				<Tag color={getColor(tag)} size="small">
					<TagName {tag} small />
				</Tag>
			{/each}
		{:else if $postStore.tags.length}
			{#each $postStore.tags as tag (tag.id)}
				<Tag size="small">
					<TagName {tag} small />
				</Tag>
			{/each}
		{:else}
			<Text light small>No tags</Text>
		{/if}
	</div>
</SplitControl>

<style>
	.wrap {
		display: flex;
		flex-wrap: wrap;
		gap: 5px;
	}
	.wrap :global(.color-red:before) {
		content: '-';
		margin-right: 5px;
	}
	.wrap :global(.color-green:before) {
		content: '+';
		margin-right: 5px;
	}
</style>
