<script lang="ts">
	import Links from './Links/Links.svelte';
	import { Loader, TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconCardChecklist, IconGear, IconLink45deg } from "@hyvor/icons";
	import UpgradeRequired from "../../billing/UpgradeRequired.svelte";
	import Settings from "./Settings.svelte";
	import { getStats } from "./linkAnalysisActions";
	import Overview from "./Overview/Overview.svelte";

    let tab: 'overview' | 'links' | 'settings' = 'overview';

    const statsPromise = getStats();
    
</script>
<div class="link-analysis hds-box">

    <UpgradeRequired
        minPlan="growth"
        trialAllowed={true}
    >

        <div slot="upgrade-text">
            Link Analysis is available on the <b>Growth plan</b> and above. Upgrade now to automatically analyze all links in your blog and receive email reports.
        </div>


        <TabNav bind:active={tab}>
            
            <TabNavItem name="overview">
                <IconCardChecklist slot="start" />
                Overview
            </TabNavItem>

            <TabNavItem name="links">
                <IconLink45deg slot="start" />
                Links
            </TabNavItem>

            <TabNavItem name="settings">
                <IconGear slot="start" />
                Settings
            </TabNavItem>
            
        </TabNav>

        <div class="content">

            {#await statsPromise}
                <Loader block padding={60} />
            {:then stats}
                {#if tab === 'overview'}
                    <Overview 
                        {stats} 
                        on:links={() => tab = 'links'} 
                    />
                {:else if tab === 'links'}
                    <Links {stats} />
                {:else if tab === 'settings'}
                    <Settings />
                {/if}
            {/await}

        </div>

    </UpgradeRequired>

</div>

<style>
    .link-analysis {
        height: 100%;
        overflow: auto;
        padding: 20px 30px;
    }
    .content {
        margin-top: 25px;
    }
</style>