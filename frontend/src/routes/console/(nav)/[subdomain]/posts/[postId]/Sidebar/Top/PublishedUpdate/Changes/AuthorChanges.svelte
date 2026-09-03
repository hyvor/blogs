<script lang="ts">
	import { SplitControl, Tag, Text } from '@hyvor/design/components';
	import type { User as UserType } from '../../../../../../../../lib/types';
	import { postOriginalStore, postStore } from '../../../../../postStore';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		diff: boolean;
	}

	let { diff }: Props = $props();

	let allAuthors: UserType[] = [];

	$postOriginalStore.authors.map((author) => allAuthors.push(author));
	$postStore.authors.map((author) =>
		allAuthors.find((t) => t.id === author.id) ? null : allAuthors.push(author)
	);

	function userFoundIn(tag: UserType, in_: UserType[]) {
		return in_.find((t) => t.id === tag.id);
	}

	function getColor(tag: UserType) {
		if (userFoundIn(tag, $postOriginalStore.authors) && userFoundIn(tag, $postStore.authors)) {
			return 'default';
		}

		if (userFoundIn(tag, $postOriginalStore.authors)) {
			return 'red';
		}

		if (userFoundIn(tag, $postStore.authors)) {
			return 'green';
		}

		return 'default';
	}
</script>

<SplitControl label={i18n.t('console.postEditor.update.authors')}>
	<div class="wrap">
		{#if diff}
			{#each allAuthors as author (author.id)}
				<Tag color={getColor(author)} size="small">
					{author.variants[0]?.name || ''}
				</Tag>
			{/each}
		{:else if $postStore.authors.length}
			{#each $postStore.authors as author (author.id)}
				<Tag size="small">
					{author.variants[0]?.name || ''}
				</Tag>
			{/each}
		{:else}
			<Text light small>{i18n.t('console.postEditor.update.noAuthors')}</Text>
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
