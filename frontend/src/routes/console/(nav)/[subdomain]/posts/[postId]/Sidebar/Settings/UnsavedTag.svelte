<script lang="ts">
	import { Loader, Tag } from "@hyvor/design/components";

    export let show : boolean = true;
    export let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';
    export let size: 'x-small' | 'small' = 'x-small'
    export let addMarginTop: boolean = true;

</script>

<div
    class:add-margin={(show || loaderState !== 'none') && addMarginTop}
>
    {#if show}
        <Tag {size} color="orange">
            Unsaved
        </Tag>
    {/if}
    <Loader state={loaderState} size={12} />
</div>

<style>
    div {
        display: flex;
        gap: 5px;
    }
    div.add-margin {
        margin-top:5px;
    }
</style>