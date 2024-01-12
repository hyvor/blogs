<script lang="ts">
	import { IconMessage, Loader } from "@hyvor/design/components";
	import { onMount } from "svelte";
	import { getUrlData } from "../../../../../../../lib/actions/urlDataActions";
	import EmbedHtmlDisplay from "../../plugins/slash/Embed/EmbedHtmlDisplay.svelte";
	import BookmarkDisplay from "../../plugins/slash/Bookmark/BookmarkDisplay.svelte";
	import type { UrlData } from "../../../../../../../lib/types";

    export let url: string;

    export let type : 'embed' | 'link' = 'embed';

    let isLoading = true;
    let urlData: UrlData;
    let error : null | string = null;

    onMount(() => {
        getUrlData(url, type)
            .then(res => {
                urlData = res;
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
        {#if type === 'embed'}
            <EmbedHtmlDisplay html={urlData.html} />
        {:else}
            <BookmarkDisplay {urlData} />
        {/if}
    {/if}
</div>

<style>
</style>