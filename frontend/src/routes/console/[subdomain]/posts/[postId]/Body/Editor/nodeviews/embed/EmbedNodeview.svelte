<script lang="ts">
	import { IconMessage, Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { getUrlData } from "../../../../../../../lib/actions/urlDataActions";
	import EmbedHtmlDisplay from "../../plugins/slash/Embed/EmbedHtmlDisplay.svelte";

    export let url: string;

    let isLoading = true;
    let html = '';
    let error : null | string = null;

    onMount(() => {
        getUrlData(url, 'embed')
            .then(res => {
                html = res.html!;
            })
            .catch(() => {
                error = 'Failed to load embed';
            })
            .finally(() => {
                isLoading = false;
            })
    })

</script>


<div>
    {#if isLoading}
        <Loader block padding={100} />
    {:else if error}
        <IconMessage 
            error 
            padding={60} 
            message={error}
            iconSize={70}
        />
    {:else}
        <EmbedHtmlDisplay {html} />
    {/if}
</div>

<style>
    div {
        background: var(--input);
    }
</style>