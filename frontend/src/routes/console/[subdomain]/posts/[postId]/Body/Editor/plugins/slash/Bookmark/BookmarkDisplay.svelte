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
    <div class="link-thumbnail">
        <img alt="Thumbnail" src="{urlData.thumbnail_url}" />
    </div>
</div>

<style lang="scss">
    .wrap {
        cursor: default;
        display: flex;
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 0 5px rgb(0 0 0 / 6%);
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