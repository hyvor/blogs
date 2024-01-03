<script lang="ts">
	import { Button, Loader, TextInput, toast } from "@hyvor/design/components";
import MediaFilter from "./MediaFilter.svelte";
	import { getMedia, type FileType } from "./mediaActions";
	import { IconCloudUpload } from "@hyvor/icons";
	import type { Media } from "../../../lib/types";
	import { onMount } from "svelte";
	import MediaFile from "./MediaFile.svelte";

    let isLoading = true;
    let mediaFiles: Media[] = [];

    function load(extensions: string[] = [], search: string | null = null) {
        isLoading = true;

        getMedia(extensions, search)
            .then(res => {
                isLoading = false;
                mediaFiles = res;
            })
            .catch(err => {
                isLoading = false;
                toast.error(err.message)
            });
    }

    function handleChange(e: CustomEvent<{extensions: string[], search: string | null}>) {
        load(e.detail.extensions, e.detail.search);
    }

    onMount(() => {
        load();
    });

</script>

<div class="topbar hds-box">
    <MediaFilter on:change={handleChange} />

    <Button>
        <IconCloudUpload slot="start" />
        Upload
    </Button>
</div>

<div class="media-show hds-box">

    {#if isLoading}
        <Loader full />
    {:else}

        {#if !mediaFiles.length}
            No media found
            <!-- TODO: Add IconMessage -->
        {:else}
            {#each mediaFiles as media (media.id)}
                <MediaFile {media} />
            {/each}
        {/if}

    {/if}

</div>

<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        margin-bottom: 15px;
    }
    .media-show {
        flex: 1;
        padding: 25px;
        overflow: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 15px 10px;
    }
</style>