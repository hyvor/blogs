<script lang="ts">
	import { Loader, Tag } from "@hyvor/design/components";

    export let show : boolean = true;
    export let loaderState : 'none' | 'loading' | 'success' | 'error' = 'none';

</script>

<div
    class:shown={show || loaderState !== 'none'}
>
    {#if show}
        <Tag size="x-small" color="orange">
            Unsaved
        </Tag>
    {/if}
    <Loader state={loaderState} size="small" />
</div>

<style>
    div {
        display: flex;
        gap: 5px;
    }
    div.shown {
        margin-top:5px;
    }
</style>