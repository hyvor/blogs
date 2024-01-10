<script lang="ts">
	import { ActionList, ActionListItem, Button, Dropdown, IconButton, Text } from "@hyvor/design/components";
	import { IconCaretDown, IconCheck, IconHourglass, IconJournalText, IconStar, IconX } from "@hyvor/icons";
	import { postListFiltersStore, setFilter } from "../postListStore";
	import { blogCountsStore } from "../../../lib/stores/blogStore";

    const status = ['draft', 'published', 'scheduled', 'featured'];

    let showDropdown = false;

    function handleSelect(item: string) {
        setFilter('status', $postListFiltersStore.status === item ? null : item as any);
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

    <Button slot="trigger" color="input">
        <Text bold slot="start">Status</Text>

        <span class="text">
            {$postListFiltersStore.status || 'Any'}
        </span>

        {#if $postListFiltersStore.status}
            <IconButton 
                size={14} 
                style="margin-left:6px;"
                on:click={handleX}
            >
                <IconX size={12} />
            </IconButton>
        {/if}

        <IconCaretDown slot="end" size={14} />
    </Button>

    <ActionList 
        slot="content" 
    >

        {#each status as item}
            <ActionListItem
                on:select={() => handleSelect(item)}
                style="
                    text-transform:capitalize;
                    {$postListFiltersStore.status === item ? 'background-color: var(--accent-light-mid)' : ''}
                "
            >
                <span slot="start">
                    {#if item === 'published'}
                        <IconCheck slot="start" size={12} />
                    {:else if item === 'draft'}
                        <IconJournalText slot="start" size={12} />
                    {:else if item === 'scheduled'}
                        <IconHourglass slot="start" size={12} />
                    {:else if item === 'featured'}
                        <IconStar slot="start" size={12} />
                    {/if}
                </span>
                {item}
                <span slot="end">{getPostsCount(item)}</span>
            </ActionListItem>
        {/each}

    </ActionList>

</Dropdown>

<style>
    .text {
        display: inline-block;
        font-weight: normal;
        text-transform: capitalize;
        vertical-align: middle;
    }
</style>