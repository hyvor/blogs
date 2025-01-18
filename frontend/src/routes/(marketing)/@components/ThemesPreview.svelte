<script lang="ts">
    import { run } from 'svelte/legacy';

	import { createEventDispatcher, onMount } from "svelte";
	import type { Theme } from "../../console/lib/types";
	import { loadThemes } from "../../console/(nav)/[subdomain]/theme/themeActions";
	import { getConfig, loadConfig } from "../../console/lib/config";
	import { IconButton, IconMessage, Link, Loader, NavLink, Text, Button } from "@hyvor/design/components";
	import { IconBoxArrowUpRight, IconCaretDown, IconLaptop, IconList, IconLock, IconTablet, IconThreeDots, IconGithub } from "@hyvor/icons";

    interface Props {
        lockScroll?: boolean;
    }

    let { lockScroll = $bindable(false) }: Props = $props();

    let isLoaded = $state(false);
    let themes: Theme[] = $state([]);



    let port: string = $state("");
    let type : 'laptop' | 'tablet' = $state('laptop');

    let isLoading = $state(true);

    const dispatch = createEventDispatcher();


    onMount(async () => {
        await loadConfig();
        themes = await loadThemes();
        isLoaded = true;

        dispatch('load');
        
        // support local
        port = window.location.port ? `:${Number(window.location.port) + 1}` : "";
    })

    let navEl: HTMLDivElement = $state();

    function handleMobileNavClick() {
        if (!navEl) return;
        if (window.innerWidth > 992) return;

        navEl.style.display = navEl.style.display !== 'block' ? 'block' : 'none';
    }

    let originalThemes = $derived(themes.filter((theme) => theme.type === "original" && theme.name !== 'blank'));
    let portedThemes = $derived(themes.filter((theme) => theme.type === "ported"));
    let currentTheme;
    run(() => {
        currentTheme = originalThemes[0];
    });
    let currentThemeUrl = $derived(`//${currentTheme?.preview_subdomain}.${getConfig().domains?.delivery}${port}`);
</script>


{#if isLoaded}

    <div class="wrap">

        <!-- svelte-ignore a11y_missing_attribute -->
        <a 
            class="mobile-nav"
            onclick={handleMobileNavClick}
            onkeyup={e => e.key === 'Enter' && handleMobileNavClick()}
            role="button"
            tabindex="0"
        >
            <div class="mobile-nav-left">
                Choose theme
            </div>
            <span class="theme-name">{currentTheme?.name}</span>
            <IconCaretDown size={14} />
        </a>

        <div class="nav hds-box" bind:this={navEl}>
            <div>
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
                                handleMobileNavClick();
                            }}
                            active={currentTheme?.name === theme.name}
                        >
                            {theme.name}
                        </NavLink>
                    {/if}
                {/each}
            {/each}
            </div>

            <div class="open-source">
                <div class="text">
                    <Text small>Themes are open-source</Text>
                </div>
                <Button size="small" as="a" href="https://github.com/hyvor/hyvor-blogs-themes" target="_blank">
                    View Source
                    {#snippet end()}
                                        <IconGithub  size={14} />
                                    {/snippet}
                </Button>
            </div>
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
                        {#snippet end()}
                                                <IconBoxArrowUpRight  size={14} />
                                            {/snippet}
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
                        onload={() => isLoading = false}
                        style:display={isLoading ? "none" : "block"}
></iframe>

                    {#if lockScroll}
                        <button 
                            class="lock-scroll"
                            onclick={() => lockScroll = false}
                        >
                            <div class="overlay"></div>
                            <IconMessage
                                icon={IconLock}
                                iconSize={50}
                                message="Click to unlock scroll"
                            />
                        </button>
                    {/if}

                </div>
            {/if}
        </div>

    </div>


{/if}


<style lang='scss'>

    .mobile-nav {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: var(--box-background);
        border-radius: var(--box-radius);
        box-shadow: var(--box-shadow);
        cursor: pointer;
        display: none;
        .mobile-nav-left {
            flex: 1;
            color: var(--text-light);
            font-size: 14px;
        }
        .theme-name {
            font-weight: 600;
        }
        &:hover {
            background-color: var(--hover);
        }
    }

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

    .nav {
        padding-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    
    .open-source {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        .text {
            color: var(--text-light);
            margin-bottom: 5px;
        }
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

    .lock-scroll {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        cursor: pointer;
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #fafafa;
            opacity: 0.7;
            z-index: -1;
        }
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

    @media screen and (max-width: 992px) {
        .wrap {
            flex-direction: column;
        }
        .nav {
            width: 100%;
            margin-bottom: 15px;
            display: none;
        }

        .open-source{
            border-top: 1px solid var(--border);
            flex-direction: row;
            justify-content:space-around;
            margin-top: 5%;
            padding-top: 15px;
        }

        .preview {
            height: 600px;
        }
        .mobile-nav {
            display: flex;
        }
        .navi .right {
            display: none;
        }
    }

</style>