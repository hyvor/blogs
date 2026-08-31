<script lang="ts">
	import { ActionList, Button, Dropdown, IconButton, Text } from '@hyvor/design/components';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconX from '@hyvor/icons/IconX';

	import { postListFiltersStore, setFilter } from '../../postListStore';
	import { primaryLanguageStore } from '../../../../../lib/stores/languagesStore';
	import AuthorSearch from './AuthorSearch.svelte';
	import type { User } from '../../../../../lib/types';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	let showDropdown = $state(false);

	function handleSelect(e: CustomEvent<User>) {
		setFilter('author', e.detail);
		showDropdown = false;
	}

	function handleX(e: any) {
		e.stopPropagation();
		setFilter('author', null);
		showDropdown = false;
	}
</script>

<Dropdown align="end" bind:show={showDropdown} width={350}>
	{#snippet trigger()}
		<Button color="input">
			{#snippet start()}
				<Text bold>{i18n.t('console.posts.filters.authorLabel')}</Text>
			{/snippet}

			<span class="text">
				{$postListFiltersStore.author
					? $postListFiltersStore.author.variants[0]?.name || i18n.t('console.common.unnamed')
					: i18n.t('console.common.any')}
			</span>

			{#if $postListFiltersStore.author}
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
			<AuthorSearch on:select={handleSelect} />
		</ActionList>
	{/snippet}
</Dropdown>

<style>
	.text {
		display: inline-block;
		font-weight: normal;
		text-transform: capitalize;
		vertical-align: middle;
		max-width: 125px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}
</style>
