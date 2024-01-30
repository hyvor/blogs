<script lang="ts">
    export let title: null | string = null;
</script>

<div class="wrap hds-box">
    {#if title}
        <div class="title">{title}</div>
    {/if}

    <slot />
</div>

<style>
    .wrap {
        margin:0 5px;
        margin-bottom:10px;
        padding:30px;
    }
    .title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    @media (max-width: 992px) {
        .wrap {
            margin:0;
            margin-bottom:10px;
        }
    }
</style>