<script lang="ts">
    export let name: string;
</script>

<div class="meta">
    <div class="name">{name}</div>
    <div class="value"><slot /></div>
</div>

<style>
    .meta {
        display: flex;
        flex-direction: column;
        padding: 0 15px;
    }
    .meta:not(:last-child) {
    }

    .name {
        font-size: 13px;
        color: var(--text-light);
        margin-bottom: 2px;
    }
    .value {
        font-weight: 600;
    }
</style>