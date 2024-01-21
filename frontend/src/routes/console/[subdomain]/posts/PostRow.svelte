<script lang="ts">
	import dayjs from 'dayjs';
	import type { Post, PostVariant } from "../../lib/types";
	import { getLanguageById } from "../../lib/actions/languageActions";
	import { Avatar, Link, Tag } from "@hyvor/design/components";
	import PostStatusTag from "./PostStatusTag.svelte";
	import { blogStore } from "../../lib/stores/blogStore";
	import LinkAnalysisTag from "./Tags/LinkAnalysisTag.svelte";
	import SeoAnalysisTag from "./Tags/SeoAnalysisTag.svelte";
	import VariantLangTag from "./Tags/VariantLangTag.svelte";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import { consoleUrlWithBlog } from "../../lib/consoleUrl";
    
    export let post: Post;

    $: variant = post.variants[0]!;

    const publishedAtDate = dayjs.unix(post.published_at || post.created_at).format('MMM D, YYYY');
    const createdAtDate = dayjs.unix(post.created_at).format('MMM D, YYYY');

    function getVariantLanguage(v: PostVariant) {
        return getLanguageById(v.language_id);
    }

</script>

<a 
    class="post-list-item"
    href={consoleUrlWithBlog(`/posts/${post.id}`)}
>

    <div>
        <div class="post-title">{variant?.title || '(Untitled)'}</div>

        <div class="post-slug">
            {#if variant?.slug && variant.status === 'published'}
                <a 
                    href={post.variants[0]?.url || ''}
                    target="_blank"
                >
                    {post.variants[0]?.slug || ''}
                    <IconBoxArrowUpRight size={10} style="margin-left: 4px;" />
                </a>
            {/if}
        </div>

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
            {#if variant?.status === 'published' && post.updated_at !== post.published_at}
                <div class="post-date">
                    Updated {dayjs.unix(post.updated_at).format('MMM D, YYYY')}
                </div>
            {/if}
        </div>

    </div>

    <div class="post-languages">

        {#each post.variants as variant (variant.id)}
            <VariantLangTag {variant} />
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
                    <Tag size="small">
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
            <SeoAnalysisTag postVariant={variant} />
        </div>
        <div class="links">
            <span class="name">Links</span>
            <LinkAnalysisTag postVariant={variant} />
        </div>
    </div>

    <div class="post-status-wrap">
        <PostStatusTag status={variant?.status || 'draft'} />
    </div>

</a>


<style lang="scss">

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

    .post-title {
        width: 300px;
        font-weight: 600;
        word-break: break-all;
    }

    .post-slug {
        margin-top: 4px;
        font-size: 12px;
    }
    .post-slug a {
        color: var(--link);
        &:hover {
            text-decoration: underline;
        }
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
        display: inline-flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .post-tags-wrap {
        display: flex;
    }

    .post-status-wrap {
        text-align: right;
    }

    .post-tags {
        flex: 1;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 5px;
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
        margin-top: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

</style>