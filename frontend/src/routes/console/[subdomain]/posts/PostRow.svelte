<script lang="ts">
	import dayjs from 'dayjs';
	import type { Post } from "../../lib/types";
	import { getLanguageById } from "../../lib/actions/languageActions";
	import { Avatar, Tag } from "@hyvor/design/components";
	import SeoScoreTag from "./[postId]/Post/Sidebar/Seo/SeoScoreTag.svelte";
	import PostStatusTag from "./PostStatusTag.svelte";
	import { blogStore } from "../../lib/stores/blogStore";
    export let post: Post;

    $: variant = post.variants[0];

    const publishedAtDate = dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY');
    const createdAtDate = dayjs.unix(post.created_at).format('MMM D, YYYY');

</script>

<a 
    class="post-list-item"
    href={`/console/${$blogStore.subdomain}/posts/${post.id}`}
>

    <div>
        <div class="post-title">{variant?.title || '(Untitled)'}</div>

        <div class="post-data">
            <div class="post-date">
                {#if variant?.status === 'published'}
                    Published {publishedAtDate}
                {:else if variant?.status === 'scheduled'}
                    Scheduled {publishedAtDate}
                {:else}
                    Created {createdAtDate}
                {/if}
            </div>
            {#if variant?.status === 'published'}
                <div class="post-date">
                    Updated {dayjs.unix(post.updated_at).format('MMM D, YYYY')}
                </div>
            {/if}
        </div>

    </div>

    <div class="post-languages">

        {#each post.variants as variant (variant.id)}
            <Tag size="small">
                { getLanguageById(variant.language_id)?.code || '' }
            </Tag>
        {/each}

    </div>

    {#if !post.is_page}

        <div class="post-authors">

            {#each post.authors as author (author.id)}
                <div class="post-author">
                    <Avatar 
                        src={author.picture_url || undefined} 
                        size="small"
                    />
                    <span class="post-author-name">
                        {author.variants[0]?.name || 'Unnamed'}
                    </span>
                </div>
            {/each}
        </div>

    {/if}

    <div class="post-tags-wrap">

        <div class="post-tags">
            {#if !post.is_page}
                {#each post.tags as tag (tag.id)}
                    <Tag size="small" outline fill>
                        {tag.variants[0]?.name || null}
                    </Tag>
                {/each}
            {/if}
        </div>
    </div>

    <div class="post-health-wrap">
        <div class="seo">
            <span class="name">SEO</span>
            <!-- <SeoScoreTag
                score={variant?.average} 
                percentage={true} 
            /> -->
        </div>
        <div class="links">
            <span class="name">Links</span>
            <!-- <LinkTag /> -->
        </div>
    </div>

    <div class="post-status-wrap">
        <PostStatusTag status={variant?.status || 'draft'} />
    </div>

</a>


<style>

    .post-list-item {
        display: grid;
        align-items: center;
        grid-template-columns: 1fr 1fr 1fr 1fr 100px 120px;
        padding: 20px;
        border-left: 3px solid transparent;
        position: relative;
        border-radius: 20px;
        cursor: pointer;
    }

    .post-list-item > div {
        padding-right: 8px;
    }

    .post-list-item:hover {
        background: var(--hover);
    }

    /* &:not(:last-child):after {
        // border-bottom: 1px solid $color-accent-very-light;
        content: "";
        position: absolute;
        justify-self: center;
        width: 95%;
        background: $color-accent-very-light;
        bottom: 0;
        height: 1px;
    } */

    .post-title {
        width: 300px;
        font-weight: 600;
        word-break: break-all;
    }

    .post-data {
        margin-top: 4px;
    }

    .post-author,
    .post-date {
        display: flex;
        margin-top: 1px;
        align-items: center;
    }

    .post-author:not(:first-child) {
        margin-top: 6px;
    }

    .post-date {
        color: var(--text-light);
        font-size: 12px;
    }

    .post-author-name {
        margin-left: 7px;
        font-size: 14px;
    }


    .post-status-wrap {
        margin-bottom: 4px;
        text-align: right;
    }

    .post-languages {
        margin-top: 8px;
    }

    .post-tags-wrap {
        display: flex;
    }

    .post-status-wrap {
        text-align: right;
    }

    .post-tags {
        flex: 1;
    }

    .post-health-wrap {
        display: inline-flex;
        flex-direction: column;
        
    }

    .post-health-wrap .name {
        font-size: 12px;
        color: var(--text-light);
        margin-right: 5px;
    }

    .post-health-wrap .links {
        margin-top: 2px;
    }

</style>