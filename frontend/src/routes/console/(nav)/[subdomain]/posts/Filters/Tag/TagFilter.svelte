<script lang="ts">
	import { ActionList, Button, Dropdown, IconButton, Text } from "@hyvor/design/components";
	import { IconCaretDown, IconX } from "@hyvor/icons";
	import { postListFiltersStore, setFilter } from "../../postListStore";
	import TagSearch from "./TagSearch.svelte";
	import type { Tag } from "../../../../../lib/types";

    let showDropdown = false;

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

    <Button slot="trigger" color="input">
        <Text bold slot="start">Tag</Text>

        <span class="text">
            {
                $postListFiltersStore.tag ?
                    $postListFiltersStore.tag.variants[0]?.name || 'Unnamed' :
                    'Any'
            }
        </span>

        {#if $postListFiltersStore.tag}
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

    <ActionList slot="content">
        <TagSearch 
            on:select={handleSelect}
        />
    </ActionList>

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