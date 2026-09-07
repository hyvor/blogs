<script lang="ts">
	import { ActionList, Button, Dropdown, IconButton, Text } from '@hyvor/design/components';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconX from '@hyvor/icons/IconX';

	import { postListFiltersStore, setFilter } from '../../postListStore';
	import TagSearch from './TagSearch.svelte';
	import type { Tag } from '../../../../../lib/types';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let showDropdown = $state(false);

	function handleSelect(e: CustomEvent<Tag>) {
		setFilter('tag', e.detail);
		showDropdown = false;
	}

	function handleX(e: any) {
		e.stopPropagation();
		setFilter('tag', null);
		showDropdown = false;
	}
</script>

<Dropdown align="end" bind:show={showDropdown} width={350}>
	{#snippet trigger()}
		<Button color="input">
			{#snippet start()}
				<Text bold>{i18n.t('console.posts.filters.tagLabel')}</Text>
			{/snippet}

			<span class="text">
				{$postListFiltersStore.tag
					? $postListFiltersStore.tag.variants[0]?.name || i18n.t('console.common.unnamed')
					: i18n.t('console.common.any')}
			</span>

			{#if $postListFiltersStore.tag}
				<IconButton size={14} style="margin-left:6px;" on:click={handleX}>
					<IconX size={12} />
				</IconButton>
			{/if}

			{#snippet end()}
				<IconCaretDown size={14} />
			{/snippet}
		</Button>
	{/snippet}

	{#snippet content()}
		<ActionList>
			<TagSearch on:select={handleSelect} />
		</ActionList>
	{/snippet}
</Dropdown>

<style>
	.text {
		display: inline-block;
		font-weight: normal;
		vertical-align: middle;
		max-width: 125px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
</style>
