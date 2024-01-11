<script lang="ts">
	import { Button, IconMessage, Loader, toast } from "@hyvor/design/components";
    import MediaFilter from "./MediaFilter.svelte";
	import { getMedia, type FileType } from "./mediaActions";
	import { IconCloudUpload } from "@hyvor/icons";
	import type { Media } from "../../../lib/types";
	import { onMount } from "svelte";
	import MediaFile from "./MediaFile.svelte";

    export let showUpload = true;
    export let filterDefaultType = null as null | FileType;
    export let filterTypeDisabled = false;

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

<div class="wrap">

    <div class="topbar">
        <MediaFilter 
            on:change={handleChange} 
            defaultType={filterDefaultType}
            typeDisabled={filterTypeDisabled}
        />

        {#if showUpload}
            <Button>
                <IconCloudUpload slot="start" />
                Upload
            </Button>
        {/if}
    </div>

    <div class="media-show">

        {#if isLoading}
            <Loader full />
        {:else}

            {#if !mediaFiles.length}
                <IconMessage empty message="No Media Found" />
            {:else}
                {#each mediaFiles as media (media.id)}
                    <MediaFile {media} />
                {/each}
            {/if}

        {/if}

    </div>

</div>

<style>
    .wrap {
        flex: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
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