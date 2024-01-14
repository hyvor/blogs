<script lang="ts">
	import { TabNav, TabNavItem } from "@hyvor/design/components";
	import NewImport from "./NewImport/NewImport.svelte";
	import ImportHistory from "./History/ImportHistory.svelte";

    let tab: 'new' | 'history' = 'new';

    function handleComplete() {
        tab = 'history';
    }

</script>
<div class="import hds-box">

    <TabNav bind:active={tab}>
        
        <TabNavItem name="new">
            New Import
        </TabNavItem>
        
        <TabNavItem name="history">
            History
        </TabNavItem>

    </TabNav>

    <div class="content">

        {#if tab === 'new'}
            <NewImport on:complete={handleComplete} />
        {:else if tab === 'history'}
            <ImportHistory />
        {/if}

    </div>

</div>

<style>

    .import {
        height: 100%;
        overflow: auto;
        padding: 20px 30px;
    }

    .content {
        padding: 20px 0;
    }

</style>