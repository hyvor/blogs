<script lang="ts">
	import Links from './Links/Links.svelte';
	import { TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconGear, IconLink45deg, IconMagic, IconSearchHeart } from "@hyvor/icons";
	import Settings from "./Settings/Settings.svelte";
	import SeoScoreTag from "./Seo/SeoScoreTag.svelte";
	import Seo from "./Seo/Seo.svelte";
	import { variantSeoStore } from "../../seoStore";
	import Ai from "./Ai/Ai.svelte";
	import { Z_INDEX, increaseZIndex } from "../z-index";
	import SidebarTop from "./Top/SidebarTop.svelte";
	import { tab } from "./sidebar";

    let div: HTMLDivElement;

    function handleClick() {
        div.style.zIndex = Z_INDEX + 1 + "";
        increaseZIndex();
    }

</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<div 
    class="post-sidebar"
    on:click={handleClick}
    bind:this={div}
>

    <SidebarTop />

    <div class="body hds-box">

        <div class="nav">

            <TabNav bind:active={$tab}>

                <TabNavItem name="settings">
                    <IconGear slot="start" />
                    Settings
                </TabNavItem>

                <TabNavItem name="seo">
                    <IconSearchHeart slot="start" />
                    SEO
                    <SeoScoreTag score={$variantSeoStore.average} percentage slot="end" />
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

            {#if $tab === 'settings'}
                <Settings />
            {:else if $tab === 'seo'}
                <Seo />
            {:else if $tab === 'links'}
                <Links />
            {:else if $tab === 'ai'}
                <Ai />
            {/if}

        </div>

    </div>

</div>

<style>

    .post-sidebar {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .body {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .nav {
        padding: 15px 25px 0px;
        overflow: auto;
        font-size: 14px;
    }

    .content {
        padding: 15px 25px;
        flex: 1;
        min-height: 0;
        overflow: auto;
    }

</style>