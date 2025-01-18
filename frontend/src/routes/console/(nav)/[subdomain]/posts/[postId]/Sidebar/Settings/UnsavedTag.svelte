<script lang="ts">
	import { Loader, Tag } from "@hyvor/design/components";

    interface Props {
        show?: boolean;
        loaderState?: 'none' | 'loading' | 'success' | 'error';
        size?: 'x-small' | 'small';
        addMarginTop?: boolean;
    }

    let {
        show = true,
        loaderState = 'none',
        size = 'x-small',
        addMarginTop = true
    }: Props = $props();

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