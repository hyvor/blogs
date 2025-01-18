<script lang="ts">
    interface Props {
        left?: import('svelte').Snippet;
        right?: import('svelte').Snippet;
    }

    let { left, right }: Props = $props();


</script>

<div class="wrap">

    <div class="left">
        {@render left?.()}
    </div>

    <div class="right">
        {@render right?.()}
    </div>

</div>

<style>

    .wrap :global(img) {
        max-width: 100%;
        max-height: 500px;
    }

    .wrap {
        display: flex;
        margin: 80px 0px;
    }

    .left {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .right {
        flex: 1;
        min-width: 0;
    }

</style>