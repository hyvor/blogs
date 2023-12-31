<script lang="ts">
	import { TabNav, TabNavItem } from "@hyvor/design/components";
	import { IconGear, IconLink45deg, IconMagic, IconSearchHeart } from "@hyvor/icons";
	import Settings from "./Settings/Settings.svelte";
	import SeoScoreTag from "./Seo/SeoScoreTag.svelte";
	import Seo from "./Seo/Seo.svelte";
	import { variantSeoStore } from "../../../../lib/stores/seoStore";
	import Ai from "./Ai/Ai.svelte";
	import { Z_INDEX, increaseZIndex } from "../z-index";

    export let tab: 'settings' | 'seo' | 'links' | 'ai' = 'settings';

    let div: HTMLDivElement;

    function handleClick() {
        div.style.zIndex = Z_INDEX + 1 + "";
        increaseZIndex();
    }

</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<div 
    class="post-sidebar hds-box"
    on:click={handleClick}
    bind:this={div}
>

    <div class="nav">

        <TabNav bind:active={tab}>

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

        {#if tab === 'settings'}
            <Settings />
        {:else if tab === 'seo'}
            <Seo />
        {:else if tab === 'links'}

        {:else if tab === 'ai'}
            <Ai />
        {/if}

    </div>

</div>

<style>

    .post-sidebar {
        height: 100%;
        display: flex;
        flex-direction: column;
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