<script lang="ts">
	import dayjs from "dayjs";
	import type { Media } from "../../../lib/types";
	import { IMAGE_EXTENSIONS } from "./mediaActions";
	import { IconButton } from "@hyvor/design/components";
	import { IconTrash } from "@hyvor/icons";
	import { createEventDispatcher } from "svelte";
    
    export let media: Media;

    // If the user is selecting media files
    // delete button will not be shown
    // an event will be fired when the user selects a media file
    export let selecting = false;

    const dispatch = createEventDispatcher<{select: Media}>();

    function handleClick(e: any) {
        if (selecting) {
            e.preventDefault();
            dispatch('select', media);
        }
    }

    function handleDelete() {
        console.log('delete', media);
    }

    const isImage = IMAGE_EXTENSIONS.indexOf(media.extension) !== -1;
    const uploadedAt = dayjs.unix(media.uploaded_at);

</script>

<div class="media-file">

    <a
        class="body"
        href={media.url}
        target="_blank"
        on:click={handleClick}
    >

        {#if isImage}
            <img src={media.url} alt={media.name} />
        {:else}
            <div class="extension">
                {media.extension}
            </div>
        {/if}

    </a>

    <div class="footer">

        <div 
            class="media-name"
            title={media.original_name}
        >{media.original_name}</div>
        <time 
            class="media-at"
            datetime={uploadedAt.format()}
            title={uploadedAt.format('YYYY-MM-DD HH:mm:ss')}
        >{ uploadedAt.format('YYYY-MM-DD') }</time>

    </div>

    {#if !selecting}
        <span class="media-delete">
            <IconButton 
                on:click={handleDelete}
                size="small"
                color="red"
                variant="invisible"
            >
                <IconTrash size={10} />
            </IconButton>
        </span>
    {/if}

</div>

<style>

    .media-file {
        width: calc(25% - 10px);
        height: 240px;
        display: flex;
        flex-direction: column;
        position: relative
    }

    .body {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        background-color: var(--input);
        border-radius: var(--box-radius);
    }

    .footer {
        font-size: 12px;
        color: var(--text-light);
        text-align: center;
        margin-top: 5px;
        padding: 0 5px;
    }

    .media-name {
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }

    .media-delete {
        position: absolute;
        top: 10px;
        right: 10px;
    }

    .extension {
        font-size: 24px;
        font-weight: 600;
        color: var(--text-light);
    }

    img {
        max-width: 100%;
        max-height: 100%;
    }

</style>