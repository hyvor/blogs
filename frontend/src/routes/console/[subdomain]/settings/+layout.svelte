<script>
	import { NavLink } from "@hyvor/design/components";
	import { page } from "$app/stores";
	import { blogStore } from "../../lib/stores/blogStore";
	import { IconChatText, IconCodeSlash, IconCursorText, IconDatabase, IconExclamationTriangle, IconGlobe, IconImage, IconKey, IconLightbulb, IconLink45deg, IconList, IconPeople, IconSearchHeart, IconSend, IconSignTurnRight, IconSignTurnSlightRight, IconSignpost2, IconSlash, IconTag } from "@hyvor/icons";

    const prefix = `/console/${$blogStore.subdomain}/settings`;
</script>

<div class="settings">

    <div class="nav hds-box">

        <NavLink 
            href={`${prefix}`}
            active={$page.url.pathname === `${prefix}`}
        >
            <IconList slot="start" />
            General
        </NavLink>

        <NavLink 
            href={`${prefix}/users`}
            active={$page.url.pathname === `${prefix}/users`}
        >
            <IconPeople slot="start" />
            Users
        </NavLink>

        <NavLink 
            href={`${prefix}/tags`}
            active={$page.url.pathname === `${prefix}/tags`}
        >
            <IconTag slot="start" />
            Tags
        </NavLink>

        <NavLink 
            href={`${prefix}/languages`}
            active={$page.url.pathname === `${prefix}/languages`}
        >
            <IconGlobe slot="start" />
            Languages
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={`${prefix}/hosting`}
            active={$page.url.pathname === `${prefix}/hosting`}
        >
            <IconDatabase slot="start" />
            Hosting
        </NavLink>

        <NavLink 
            href={`${prefix}/seo`}
            active={$page.url.pathname === `${prefix}/seo`}
        >
            <IconSearchHeart slot="start" />
            SEO
        </NavLink>

        <NavLink 
            href={`${prefix}/color-mode`}
            active={$page.url.pathname === `${prefix}/color-mode`}
        >
            <IconLightbulb slot="start" />
            Light & Dark Modes
        </NavLink>

        <NavLink 
            href={`${prefix}/post-content`}
            active={$page.url.pathname === `${prefix}/post-content`}
        >
            <IconCursorText slot="start" />
            Post Content
        </NavLink>

        <NavLink 
            href={`${prefix}/navigation`}
            active={$page.url.pathname === `${prefix}/navigation`}
        >
            <IconLink45deg slot="start" />
            Navigation Links
        </NavLink>

        <NavLink 
            href={`${prefix}/media`}
            active={$page.url.pathname === `${prefix}/media`}
        >
            <IconImage slot="start" />
            Media
        </NavLink>

        <NavLink 
            href={`${prefix}/redirects`}
            active={$page.url.pathname === `${prefix}/redirects`}
        >
            <IconSignTurnSlightRight slot="start" />
            Redirects
        </NavLink>

        <NavLink 
            href={`${prefix}/routes`}
            active={$page.url.pathname === `${prefix}/routes`}
        >
            <IconSignpost2 slot="start" />
            Routes
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={`${prefix}/comments`}
            active={$page.url.pathname === `${prefix}/comments`}
        >
            <IconChatText slot="start" />
            Comments & Newsletter
        </NavLink>
        <NavLink 
            href={`${prefix}/code`}
            active={$page.url.pathname === `${prefix}/code`}
        >
            <IconCodeSlash slot="start" />
            Custom Code
        </NavLink>
        <NavLink 
            href={`${prefix}/api-keys`}
            active={$page.url.pathname === `${prefix}/api-keys`}
        >
            <IconKey slot="start" />
            API Keys
        </NavLink>
        <NavLink 
            href={`${prefix}/webhooks`}
            active={$page.url.pathname === `${prefix}/webhooks`}
        >
            <IconSend slot="start" />
            Webhooks
        </NavLink>

        <div class="section-div"></div>

        <NavLink 
            href={`${prefix}/danger`}
            active={$page.url.pathname === `${prefix}/danger`}
        >
            <IconExclamationTriangle slot="start" />
            Danger Zone
        </NavLink>
    </div>

    <div class="content hds-box">
        <slot />
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
</style>