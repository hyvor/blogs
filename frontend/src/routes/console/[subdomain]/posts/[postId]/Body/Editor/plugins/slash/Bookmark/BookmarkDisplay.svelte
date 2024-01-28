<script lang="ts">
	import type { UrlData } from "../../../../../../../../lib/types";

    export let urlData: UrlData;
</script>

<div class="wrap">
    <div class="link-details">
        <div class="link-title">{urlData.title}</div>
        <div class="link-description">{urlData.description}</div>
        <div class="link-domain">{urlData.domain}</div>
    </div>
    {#if urlData.thumbnail_url}
        <div class="link-thumbnail">
            <img alt="Thumbnail" src="{urlData.thumbnail_url}" />
        </div>
    {/if}
</div>

<style lang="scss">
    .wrap {
        cursor: default;
        display: flex;
        border: 2px solid var(--input);
        border-radius: 20px;
        overflow: hidden;
        width: 100%;
        white-space: normal;
    }
    .link-thumbnail {
        flex: 1;
        position: relative;
        img {
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
    }
    .link-details {
        flex: 2;
        display: flex;
        flex-direction: column;
        padding: 15px 20px;
        .link-title {
            font-weight: 600;
        }
        .link-description {
            font-size: 14px;
            margin-top: 4px;
            color: var(--text-light);
            overflow: hidden;
            // https://stackoverflow.com/a/13924997/9059939
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .link-domain {
            font-size: 14px;
            margin-top: 12px;
        }
    }
</style>