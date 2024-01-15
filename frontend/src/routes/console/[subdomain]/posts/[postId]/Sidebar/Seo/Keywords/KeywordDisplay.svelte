<script lang="ts">
	import { IconX } from '@hyvor/icons';
	import { IconButton } from '@hyvor/design/components';
	import { Tag } from '@hyvor/design/components';
	import KeywordAdder from './KeywordAdder.svelte';
	import { createEventDispatcher } from "svelte";

    export let keyword: string;
    export let onUpdate: (keyword: string) => boolean;

    let isUpdating = false;

    const dispatch = createEventDispatcher<{remove: void}>();

    function handleUpdate(e: CustomEvent<string>) {
        keyword = e.detail;
        const success = onUpdate(keyword);
        if (success) {
            isUpdating  = false;
        }
        return success;
    }


    function handleRemove() {
        dispatch('remove');
    }

</script>

{#if isUpdating}
    <KeywordAdder
        {keyword}
        on:add={handleUpdate}
        on:close={() => isUpdating = false}
    />
{:else}
    <div class="keyword-display">
        <Tag 
            size="small"
            on:click={() => isUpdating = true}
        >{keyword}</Tag>

        <IconButton
            size={16}
            on:click={handleRemove}
            variant="invisible"
            color="gray"
        >
            <IconX size={13} />
        </IconButton>
    </div>
{/if}

<style>
    .keyword-display {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        gap: 5px;
    }
</style>