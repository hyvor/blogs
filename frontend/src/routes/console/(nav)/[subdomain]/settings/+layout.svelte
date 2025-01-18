<script lang="ts">
	import { NavLink } from "@hyvor/design/components";
	import { page } from "$app/stores";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { IconChatText, IconCodeSlash, IconCursorText, IconDatabase, IconExclamationTriangle, IconGlobe, IconImage, IconKey, IconLightbulb, IconLink45deg, IconList, IconPeople, IconSearchHeart, IconSend, IconSignTurnRight, IconSignTurnSlightRight, IconSignpost2, IconSlash, IconTag } from "@hyvor/icons";
	import { consoleUrlWithBlog } from "../../../lib/consoleUrl";
    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children }: Props = $props();

    const prefix = `/console/${$blogStore.subdomain}/settings`;
</script>

<div class="settings">

    <div class="nav hds-box">

        <NavLink 
            href={consoleUrlWithBlog('/settings')}
            active={$page.url.pathname === `${prefix}`}
        >
            {#snippet start()}
                        <IconList  />
                    {/snippet}
            General
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/users')}
            active={$page.url.pathname === `${prefix}/users`}
        >
            {#snippet start()}
                        <IconPeople  />
                    {/snippet}
            Users
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/tags')}
            active={$page.url.pathname === `${prefix}/tags`}
        >
            {#snippet start()}
                        <IconTag  />
                    {/snippet}
            Tags
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/languages')}
            active={$page.url.pathname === `${prefix}/languages`}
        >
            {#snippet start()}
                        <IconGlobe  />
                    {/snippet}
            Languages
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={consoleUrlWithBlog('/settings/hosting')}
            active={$page.url.pathname === `${prefix}/hosting`}
        >
            {#snippet start()}
                        <IconDatabase  />
                    {/snippet}
            Hosting
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/seo')}
            active={$page.url.pathname === `${prefix}/seo`}
        >
            {#snippet start()}
                        <IconSearchHeart  />
                    {/snippet}
            SEO
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/color-mode')}
            active={$page.url.pathname === `${prefix}/color-mode`}
        >
            {#snippet start()}
                        <IconLightbulb  />
                    {/snippet}
            Light & Dark Modes
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/post-content')}
            active={$page.url.pathname === `${prefix}/post-content`}
        >
            {#snippet start()}
                        <IconCursorText  />
                    {/snippet}
            Post Content
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/navigation')}
            active={$page.url.pathname === `${prefix}/navigation`}
        >
            {#snippet start()}
                        <IconLink45deg  />
                    {/snippet}
            Navigation Links
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/media')}
            active={$page.url.pathname === `${prefix}/media`}
        >
            {#snippet start()}
                        <IconImage  />
                    {/snippet}
            Media
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/redirects')}
            active={$page.url.pathname === `${prefix}/redirects`}
        >
            {#snippet start()}
                        <IconSignTurnSlightRight  />
                    {/snippet}
            Redirects
        </NavLink>

        <NavLink 
            href={consoleUrlWithBlog('/settings/routes')}
            active={$page.url.pathname === `${prefix}/routes`}
        >
            {#snippet start()}
                        <IconSignpost2  />
                    {/snippet}
            Routes
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={consoleUrlWithBlog('/settings/comments')}
            active={$page.url.pathname === `${prefix}/comments`}
        >
            {#snippet start()}
                        <IconChatText  />
                    {/snippet}
            Comments & Newsletter
        </NavLink>
        <NavLink 
            href={consoleUrlWithBlog('/settings/code')}
            active={$page.url.pathname === `${prefix}/code`}
        >
            {#snippet start()}
                        <IconCodeSlash  />
                    {/snippet}
            Custom Code
        </NavLink>
        <NavLink 
            href={consoleUrlWithBlog('/settings/api-keys')}
            active={$page.url.pathname === `${prefix}/api-keys`}
        >
            {#snippet start()}
                        <IconKey  />
                    {/snippet}
            API Keys
        </NavLink>
        <NavLink 
            href={consoleUrlWithBlog('/settings/webhooks')}
            active={$page.url.pathname === `${prefix}/webhooks`}
        >
            {#snippet start()}
                        <IconSend  />
                    {/snippet}
            Webhooks
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={consoleUrlWithBlog('/settings/danger')}
            active={$page.url.pathname === `${prefix}/danger`}
        >
            {#snippet start()}
                        <IconExclamationTriangle  />
                    {/snippet}
            Danger Zone
        </NavLink>
    </div>

    <div class="content hds-box">
        {@render children?.()}
    </div>

</div>

<style>
    .settings {
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
        overflow: auto;
    }
    .nav :global(a.active) {
        background-color: var(--accent-light-mid);
    }
    .content {
        flex: 1;
        min-width: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .section-div {
        height: 25px;
        flex-shrink: 0;
    }

    @media (max-width: 992px) {
        .settings {
            flex-direction: column;
        }
        .nav {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }

</style>