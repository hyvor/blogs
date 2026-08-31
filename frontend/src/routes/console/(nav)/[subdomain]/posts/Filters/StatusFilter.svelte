<script lang="ts">
	import {
		ActionList,
		ActionListItem,
		Button,
		Dropdown,
		IconButton,
		Text
	} from '@hyvor/design/components';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconJournalText from '@hyvor/icons/IconJournalText';
	import IconStar from '@hyvor/icons/IconStar';
	import IconX from '@hyvor/icons/IconX';

	import { postListFiltersStore, setFilter } from '../postListStore';
	import { blogCountsStore } from '../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	const STATUS_KEYS = {
		draft: 'console.posts.status.draft',
		published: 'console.posts.status.published',
		scheduled: 'console.posts.status.scheduled',
		featured: 'console.posts.status.featured'
	} as const;

	const status = Object.keys(STATUS_KEYS) as (keyof typeof STATUS_KEYS)[];

	let showDropdown = $state(false);

	function handleSelect(item: keyof typeof STATUS_KEYS) {
		setFilter('status', $postListFiltersStore.status === item ? null : (item as any));
		showDropdown = false;
	}

	function handleX(e: any) {
		e.stopPropagation();
		setFilter('status', null);
		showDropdown = false;
	}

	function getPostsCount(status: keyof typeof STATUS_KEYS) {
		return ($blogCountsStore.posts as any)[status] || 0;
	}
</script>

<Dropdown align="end" bind:show={showDropdown}>
	{#snippet trigger()}
		<Button color="input">
			{#snippet start()}
				<Text bold>{i18n.t('console.posts.filters.statusLabel')}</Text>
			{/snippet}

			<span class="text">
				{$postListFiltersStore.status
					? i18n.t(STATUS_KEYS[$postListFiltersStore.status])
					: i18n.t('console.common.any')}
			</span>

			{#if $postListFiltersStore.status}
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
			{#each status as item}
				<ActionListItem
					on:select={() => handleSelect(item)}
					style="
	                    text-transform:capitalize;
	                    {$postListFiltersStore.status === item
						? 'background-color: var(--accent-light-mid)'
						: ''}
	                "
				>
					{#snippet start()}
						<span>
							{#if item === 'published'}
								<IconCheck size={12} />
							{:else if item === 'draft'}
								<IconJournalText size={12} />
							{:else if item === 'scheduled'}
								<IconHourglass size={12} />
							{:else if item === 'featured'}
								<IconStar size={12} />
							{/if}
						</span>
					{/snippet}
					{i18n.t(STATUS_KEYS[item])}
					{#snippet end()}
						<span>{getPostsCount(item)}</span>
					{/snippet}
				</ActionListItem>
			{/each}
		</ActionList>
	{/snippet}
</Dropdown>

<style>
	.text {
		display: inline-block;
		font-weight: normal;
		text-transform: capitalize;
		vertical-align: middle;
	}
</style>
