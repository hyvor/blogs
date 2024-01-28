<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";
	import type { Theme } from "../../console/lib/types";
	import { loadThemes } from "../../console/[subdomain]/theme/themeActions";
	import { getConfig, loadConfig } from "../../console/lib/config";
	import { IconButton, Link, Loader, NavLink } from "@hyvor/design/components";
	import { IconBoxArrowUpRight, IconLaptop, IconTablet } from "@hyvor/icons";

    let isLoaded = false;
    let themes: Theme[] = [];

    $: originalThemes = themes.filter((theme) => theme.type === "original" && theme.name !== 'blank');
    $: portedThemes = themes.filter((theme) => theme.type === "ported");

    $: currentTheme = originalThemes[0];
    $: currentThemeUrl = `//${currentTheme?.preview_subdomain}.${getConfig().domains?.delivery}${port}`;

    let port: string = "";
    let type : 'laptop' | 'tablet' = 'laptop';

    let isLoading = true;

    const dispatch = createEventDispatcher();


    onMount(async () => {
        await loadConfig();
        themes = await loadThemes();
        isLoaded = true;

        dispatch('load');
        
        // support local
        port = window.location.port ? `:${Number(window.location.port) + 1}` : "";
    })

</script>


{#if isLoaded}

    <div class="wrap">

        <div class="nav hds-box">
            {#each [originalThemes, portedThemes] as group, i}
                <div class="section">
                    {#if i === 0}
                        Original
                    {:else}
                        Ported
                    {/if}
                </div>
                {#each group as theme (theme.name)}
                    {#if theme.name !== 'blank'}
                        <NavLink
                            href="javaScript:void(0)"
                            on:click={() => {
                                isLoading = true;
                                currentTheme = theme;
                            }}
                            active={currentTheme?.name === theme.name}
                        >
                            {theme.name}
                        </NavLink>
                    {/if}
                {/each}
            {/each}
        </div>

        <div class="preview hds-box">

            <div class="navi">
                <div class="left">
                    <Link 
                        href={currentThemeUrl} 
                        target="_blank" 
                        underline={false} 
                        color="text"
                    >
                        Open in new tab
                        <IconBoxArrowUpRight slot="end" size={14} />
                    </Link>
                </div>
                <div class="right">
                    <IconButton
                        on:click={() => type = 'laptop'}
                        variant={type == 'laptop' ? "fill" : "invisible"}
                    ><IconLaptop /></IconButton>
        
                    <IconButton 
                        on:click={() => type = 'tablet'}
                        variant={type == 'tablet' ? "fill" : "invisible"}
                    ><IconTablet /></IconButton>
                </div>
            </div>

            {#if currentTheme}
                <div 
                    class="iframe"
                    style="padding: {type === 'laptop' ? 0 : 15}px"
                >
                    {#if isLoading}
                        <Loader full />
                    {/if}

                    <iframe
                        src={currentThemeUrl}
                        title={currentTheme.name}
                        style:width={type === 'laptop' ? "100%" : (type === 'tablet' ? 540 : 360) + "px"}
                        style:height={type === 'laptop' ? "100%" : 740 + "px"}
                        on:load={() => isLoading = false}
                        style:display={isLoading ? "none" : "block"}
                    />
                </div>
            {/if}
        </div>

    </div>


{/if}


<style lang='scss'>

    .wrap {
        display: flex;
        height: 100%;
        gap: 15px;
    }

    .section {
        font-weight: 600;
        padding: 10px 20px;
        margin-top: 20px;
        font-size: 14px;
    }

    .nav :global(a) {
        padding: 4px 18px!important;
        font-size:14px;
    }

    .nav :global(a.active) {
        background-color: var(--accent-light-mid);
    }

    .preview {
        flex: 1;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        overflow: hidden;
        flex-direction: column;
    }

    .navi {
        padding: 15px 20px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    .left {    
        flex: 1;
        font-size: 14px;
        font-weight: 600;
    }

    .iframe {
        flex: 1;
        display:flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position:relative;
    }

    iframe {
        max-width: 100%;
        max-height: 100%;
        border: none;
        transition: .3s width, .3s height;
    }

    @keyframes preview-iframe {
        0% {opacity: 0;}
        100% {opacity: 1;}
    }

    @media screen and (max-width: 1200px) {
        .iframe, iframe {
            min-height: 600px;
        }
    }

</style>