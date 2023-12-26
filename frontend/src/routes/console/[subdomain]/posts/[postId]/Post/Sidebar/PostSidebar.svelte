<script lang="ts">
	import { TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconGear, IconLink45deg, IconMagic, IconSearchHeart } from "@hyvor/icons";
	import Settings from "./Settings/Settings.svelte";
	import SeoScoreTag from "./Seo/SeoScoreTag.svelte";

    export let tab: 'settings' | 'seo' | 'links' | 'ai' = 'settings';

</script>


<div class="post-sidebar hds-box">

    <div class="nav">

        <TabNav bind:active={tab}>

            <TabNavItem name="settings">
                <IconGear slot="start" />
                Settings
            </TabNavItem>

            <TabNavItem name="seo">
                <IconSearchHeart slot="start" />
                SEO
                <SeoScoreTag score={50} percentage slot="end" />
            </TabNavItem>

            <TabNavItem name="links">
                <IconLink45deg slot="start" />
                Links
            </TabNavItem>

            <TabNavItem name="ai">
                <IconMagic slot="start" />
                AI
            </TabNavItem>

        </TabNav>

    </div>

    <div class="content">

        {#if tab === 'settings'}
            <Settings />
        {:else if tab === 'seo'}
            
        {:else if tab === 'links'}

        {:else if tab === 'ai'}

        {/if}

    </div>

</div>

<style>

    .post-sidebar {
        height: 100%;
    }

    .nav {
        padding: 15px 25px 5px;
        overflow: auto;
        font-size: 14px;
    }

    .content {
        padding: 15px 25px;
    }

</style>