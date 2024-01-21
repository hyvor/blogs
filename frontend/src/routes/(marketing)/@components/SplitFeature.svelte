<script lang="ts">


</script>

<div class="wrap">

    <div class="left">
        <slot name="left" />
    </div>

    <div class="right">
        <slot name="right" />
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