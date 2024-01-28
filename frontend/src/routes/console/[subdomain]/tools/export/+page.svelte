<script lang="ts">
	import { TabNav, TabNavItem } from "@hyvor/design/components";
	import NewExport from "./NewExport.svelte";
	import ExportHistory from "./ExportHistory/ExportHistory.svelte";

    let tab: 'new' | 'history' = 'new';

    function handleComplete() {
        tab = 'history';    
    }
</script>

<div class="export hds-box">

    <TabNav bind:active={tab}>
        <TabNavItem name="new">
            New Export
        </TabNavItem>
        <TabNavItem name="history">
            History
        </TabNavItem>
    </TabNav>

    <div class="content">
        {#if tab === 'new'}
            <NewExport on:complete={handleComplete} />
        {:else}
            <ExportHistory />
        {/if}
    </div>

</div>

<style>
    .export {
        height: 100%;
        overflow: auto;
        padding: 20px 30px;
    }
    .content {
        padding: 20px 0;
    }
</style>