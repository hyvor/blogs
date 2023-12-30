<script>
	import { IconBoxArrowUpRight, IconChevronExpand, IconCoin, IconColumns, IconFiles, IconGear, IconHouse, IconPalette, IconPencil, IconPlugin, IconThreeDots, IconTools } from "@hyvor/icons";
	import { page } from "$app/stores";
	import { blogStore } from "../../lib/stores/blogStore";
	import { NavLink } from "@hyvor/design/components";

</script>

<a class="current-blog" href="/console/select">

    <div class="name-url">
        <div class="name">
            { $blogStore.variants[0]?.name || 'Unnamed' }
        </div>
        <div class="url">
            { $blogStore.url.replace(/https?:\/\//, '') }
        </div>
    </div>


    <div class="current-icon">
        <IconChevronExpand />
    </div>

</a>

<div class="nav-items">

    <NavLink
        href={`/console/${$blogStore.subdomain}`} 
        active={$page.url.pathname === `/console/${$blogStore.subdomain}`}
    >
        <IconHouse slot="start" />

        Home

        <a
            class="home-link"
            slot="end"
            href={$blogStore.url}
            target="_blank"
        >
            <IconBoxArrowUpRight size={12} />
        </a>

    </NavLink>

    <div class="section-div"></div>

    <NavLink
        href={`/console/${$blogStore.subdomain}/posts`} 
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/posts`)}
    >
        <IconPencil slot="start" />
        Posts
    </NavLink>

    <NavLink
        href={`/console/${$blogStore.subdomain}/pages`} 
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/pages`)}
    >
        <IconFiles slot="start" />
        Pages
    </NavLink>


    <div class="section-div"></div>

    <NavLink
        href={`/console/${$blogStore.subdomain}/theme`} 
        active={$page.url.pathname === `/console/${$blogStore.subdomain}/theme`}
    >
        <IconPalette slot="start" />
        Theme
    </NavLink>


    <NavLink
        href={`/console/${$blogStore.subdomain}/billing`}
        active={$page.url.pathname === `/console/${$blogStore.subdomain}/billing`}
    >
        <IconCoin slot="start" />
        Billing
    </NavLink>


    <NavLink
        href={`/console/${$blogStore.subdomain}/integrations`}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/integrations`)}
    >
        <IconPlugin slot="start" />
        Integrations
    </NavLink>

    <NavLink
        href={`/console/${$blogStore.subdomain}/tools`}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/tools`)}
    >
        <IconTools slot="start" />
        Tools
    </NavLink>

    <NavLink
        href={`/console/${$blogStore.subdomain}/settings`}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/settings`)}
    >
        <IconGear slot="start" />
        Settings
    </NavLink>

</div>


<style>

    .current-blog {
        display: flex;
        align-items: center;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: var(--box-radius);
        margin: 10px;
    }

    .current-blog:hover {
        background-color: var(--hover);
    }

    .name-url {
        min-width: 0;
        overflow: hidden;
        flex: 1;
    }

    .url {
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.8rem;
        color: var(--text-light);
        white-space: nowrap;
    }

    .current-icon {
        margin-left: 4px;
    }

    .nav-items {
        padding-bottom: 20px;
        padding-top: 10px;
    }

    .nav-items :global(a.active) {
        background-color: var(--accent-light-mid);
    }

    .home-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: .2s background-color, .2s box-shadow;
    }

    .home-link:hover {
        background-color: var(--accent-light);
        box-shadow: 0 0 0 6px var(--accent-light);
    }
    
    .section-div {
        height: 25px;
    }

</style>