<script lang="ts">
	import { NavLink } from "@hyvor/design/components";
	import { page } from "$app/stores";
	import { blogStore } from "../../../lib/stores/blogStore";
	import IconDownload from '@hyvor/icons/IconDownload';
import IconImage from '@hyvor/icons/IconImage';
import IconLink45deg from '@hyvor/icons/IconLink45deg';
import IconUpload from '@hyvor/icons/IconUpload';

	import { consoleUrlWithBlog } from "../../../lib/consoleUrl";
    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children }: Props = $props();
</script>

<div class="tools">

    <div class="nav hds-box">
        <NavLink 
            href={consoleUrlWithBlog('/tools/media')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/media`}
        >
            {#snippet start()}
                        <IconImage  />
                    {/snippet}
            Media Library
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/tools/link-analysis')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/link-analysis`}
        >
            {#snippet start()}
                        <IconLink45deg  />
                    {/snippet}
            Link Analysis
        </NavLink>

        <div class="div"></div>

        <NavLink 
            href={consoleUrlWithBlog('/tools/import')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/import`}
        >
            {#snippet start()}
                        <IconDownload  />
                    {/snippet}
            Import
        </NavLink>

        <NavLink
            href={consoleUrlWithBlog('/tools/export')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/export`}
        >
            {#snippet start()}
                        <IconUpload  />
                    {/snippet}
            Export
        </NavLink>
    </div>

    <div class="content">
        {@render children?.()}
    </div>

</div>

<style>
    .tools {
        display: flex;
        height: 100%;
    }
    .nav {
        width: 315px;
        margin-right: 15px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        height: 100%;
        padding: 25px 0;
    }
    .nav :global(a.active) {
        background-color: var(--accent-light-mid);
    }
    .content {
        flex: 1;
        min-width: 0;
        height: 100%;
        overflow: auto;
        display: flex;
        flex-direction: column;
    }

    .div {
        height: 25px;
    }


    @media (max-width: 992px) {
        .tools {
            flex-direction: column;
        }
        .nav {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }

</style>