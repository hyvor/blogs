<script lang="ts">
	import { Loader, Modal } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { loadThemes } from "../themeActions";
	import type { Theme } from "../../../lib/types";

    export let show = false;

    let isLoading = true;
    let themes : Theme[] = [];

    onMount(() => {

        loadThemes()
            .then(res => {
                themes = res;
            })
            .finally(() => isLoading = false);

    });
    
</script>

<Modal
    title="Choose a theme"
    bind:show={show}
>
    {#if isLoading}
        <Loader block padding={100} />
    {:else}

        {#each themes as theme (theme.id)}
            {theme.name}
        {/each}

    {/if}
</Modal>