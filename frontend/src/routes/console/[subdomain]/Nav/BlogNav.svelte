<script>
	import { IconBoxArrowUpRight, IconChevronExpand, IconCoin, IconColumns, IconFiles, IconGear, IconHouse, IconPalette, IconPencil, IconPlugin, IconThreeDots, IconTools } from "@hyvor/icons";
	import { page } from "$app/stores";
	import { blogStore } from "../../lib/stores/blogStore";
	import { NavLink } from "@hyvor/design/components";
	import { consoleUrl } from "../../lib/consoleUrl";

</script>

<a class="current-blog" href={consoleUrl('/select')}>

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
        href={consoleUrl($blogStore.subdomain)}
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
        href={consoleUrl(`${$blogStore.subdomain}/posts`)}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/posts`)}
    >
        <IconPencil slot="start" />
        Posts
    </NavLink>

    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/pages`)}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/pages`)}
    >
        <IconFiles slot="start" />
        Pages
    </NavLink>


    <div class="section-div"></div>

    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/theme`)} 
        active={$page.url.pathname === `/console/${$blogStore.subdomain}/theme`}
    >
        <IconPalette slot="start" />
        Theme
    </NavLink>


    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/billing`)}
        active={$page.url.pathname === `/console/${$blogStore.subdomain}/billing`}
    >
        <IconCoin slot="start" />
        Billing
    </NavLink>


    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/integrations`)}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/integrations`)}
    >
        <IconPlugin slot="start" />
        Integrations
    </NavLink>

    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/tools`)}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/tools`)}
    >
        <IconTools slot="start" />
        Tools
    </NavLink>

    <NavLink
        href={consoleUrl(`${$blogStore.subdomain}/settings`)}
        active={$page.url.pathname.startsWith(`/console/${$blogStore.subdomain}/settings`)}
    >
        <IconGear slot="start" />
        Settings
    </NavLink>

</div>

<style lang="scss">

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


    @media (max-width: 992px) {
        .current-blog {
            margin: 0;
            border-radius: 0;
        }
        .nav-items {
            padding: 0;
            display: flex;
            border-top: 1px solid var(--border);
            :global(a) {
                flex: 1;
                justify-content: center;
                padding: 15px 0;
                border-top: 3px solid transparent;
                border-left: none!important;
            }
            :global(a .start) {
                margin-right: 0!important;
            }
            :global(a .middle) {
                display: none;
            }
            :global(a.active) {
                border-top: 3px solid var(--accent);
            }
            :global(a .end) {
                display: none;
            }
        }
    }

</style>