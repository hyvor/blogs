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
	import IconX from '@hyvor/icons/IconX';

	import { postListFiltersStore, setFilter } from '../../postListStore';
	import dayjs from 'dayjs';
	import { OPTIONS, dateFilterStore } from './date';

	const options = Object.entries(OPTIONS) as [keyof typeof OPTIONS, string][];

	let showDropdown = $state(false);

	function handleSelect(item: keyof typeof OPTIONS) {
		dateFilterStore.set(item);

		let start = dayjs().subtract(7, 'day');
		let end = dayjs();

		if (item === 'today') {
			start = dayjs().startOf('day');
			end = dayjs();
		} else if (item === 'last_week') {
			end = dayjs().startOf('week');
			start = end.subtract(7, 'day');
		} else if (item === 'last_month') {
			end = dayjs().startOf('month');
			start = end.subtract(1, 'month');
		} else if (item === 'last_year') {
			end = dayjs().startOf('year');
			start = end.subtract(1, 'year');
		}

		postListFiltersStore.update((store) => {
			return {
				...store,
				startDate: start.toDate(),
				endDate: end.toDate()
			};
		});

		showDropdown = false;
	}

	function handleX(e: any) {
		e.stopPropagation();
		dateFilterStore.set(null);
		setFilter('startDate', null);
		setFilter('endDate', null);
		showDropdown = false;
	}
</script>

<Dropdown align="end" bind:show={showDropdown}>
	{#snippet trigger()}
		<Button color="input">
			{#snippet start()}
				<Text bold>Date</Text>
			{/snippet}

			<span class="text">
				{$dateFilterStore ? OPTIONS[$dateFilterStore] : 'Any'}
			</span>

			{#if $dateFilterStore}
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
			{#each options as [key, label] (key)}
				<ActionListItem
					on:select={() => handleSelect(key)}
					style="
                        text-transform:capitalize;
                        {$dateFilterStore === key
						? 'background-color: var(--accent-light-mid)'
						: ''}
                    "
				>
					{label}
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
