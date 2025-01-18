<script lang="ts">
	import {
		ActionList,
		ActionListItem,
		Button,
		Dropdown,
		IconButton,
		Text
	} from '@hyvor/design/components';
	import {
		IconCaretDown,
		IconCheck,
		IconHourglass,
		IconJournalText,
		IconStar,
		IconX
	} from '@hyvor/icons';
	import { postListFiltersStore, setFilter } from '../postListStore';
	import { blogCountsStore } from '../../../../lib/stores/blogStore';

	const status = ['draft', 'published', 'scheduled', 'featured'];

	let showDropdown = $state(false);

	function handleSelect(item: string) {
		setFilter('status', $postListFiltersStore.status === item ? null : (item as any));
		showDropdown = false;
	}

	function handleX(e: any) {
		e.stopPropagation();
		setFilter('status', null);
		showDropdown = false;
	}

	function getPostsCount(status: string) {
		return ($blogCountsStore.posts as any)[status] || 0;
	}
</script>

<Dropdown align="end" bind:show={showDropdown}>
	{#snippet trigger()}
		<Button color="input">
			{#snippet start()}
				<Text bold>Status</Text>
			{/snippet}

			<span class="text">
				{$postListFiltersStore.status || 'Any'}
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
					{item}
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
