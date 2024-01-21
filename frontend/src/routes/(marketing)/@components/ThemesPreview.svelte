<script lang="ts">
	import { createEventDispatcher, onMount } from "svelte";
	import type { Theme } from "../../console/lib/types";
	import { loadThemes } from "../../console/[subdomain]/theme/themeActions";
	import { getConfig, loadConfig } from "../../console/lib/config";
	import { NavLink } from "@hyvor/design/components";

    let isLoaded = false;
    let themes: Theme[] = [];

    $: originalThemes = themes.filter((theme) => theme.type === "original" && theme.name !== 'blank');
    $: portedThemes = themes.filter((theme) => theme.type === "ported");

    $: currentTheme = originalThemes[0];

    let port: string = "";

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
                            on:click={() => currentTheme = theme}
                            active={currentTheme?.name === theme.name}
                        >
                            {theme.name}
                        </NavLink>
                    {/if}
                {/each}
            {/each}
        </div>

        <div class="preview hds-box">
            {#if currentTheme}
                <iframe
                    src={`//${currentTheme.preview_subdomain}.${getConfig().domains.delivery}${port}`}
                    title={currentTheme.name}
                />
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
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    iframe {
        width: 100%;
        height: 100%;
        border: none;
        transition: .3s width, .3s height;
    }

</style>