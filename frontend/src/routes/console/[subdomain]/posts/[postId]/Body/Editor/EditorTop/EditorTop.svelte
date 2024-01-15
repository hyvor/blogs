<script lang="ts">
	import SaveStatus from './SaveStatus.svelte';
</script>

<div class="editor-top">

    <div class="left">
        <SaveStatus />
    </div>

    <div class="right">
        <!-- see plugin-wordocount.ts -->
        <span id="pm-word-count" />
    </div>

</div>

<style>
    .editor-top {
        padding: 15px 25px;
        border-bottom: 1px solid var(--border);
        display: flex;
    }
    .left {
        flex: 1;
    }
    #pm-word-count {
        font-size: 12px;
        color: var(--text-light);
        font-weight: 600;
    }
</style>