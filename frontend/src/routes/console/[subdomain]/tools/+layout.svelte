<script>
	import { NavLink } from "@hyvor/design/components";
    import hyvorTalkLogo from '$lib/img/services/hyvor-talk.svg';
	import { page } from "$app/stores";
	import { blogStore } from "../../lib/stores/blogStore";
	import { IconImage } from "@hyvor/icons";
</script>

<div class="tools">

    <div class="nav hds-box">
        <NavLink 
            href={`/console/${$blogStore.subdomain}/tools/media`}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/tools/media`}
        >
            <IconImage slot="start" />
            Media Library
        </NavLink>
    </div>

    <div class="content hds-box">
        <slot />
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
    .nav img {
        width: 20px;
        height: 20px;
        border-radius: 50%;
    }
    .nav :global(a.active) {
        background-color: var(--accent-light-mid);
    }
    .content {
        flex: 1;
        min-width: 0;
        height: 100%;
        padding: 25px 45px;
        overflow: auto;
    }
</style>