<script>
	import { NavLink } from "@hyvor/design/components";
    import hyvorTalkLogo from '$lib/img/services/hyvor-talk.svg';
	import { page } from "$app/stores";
	import { blogStore } from "../../lib/stores/blogStore";
	import { consoleUrl, consoleUrlWithBlog } from "../../lib/consoleUrl";
	import DisabledOnTemp from "../Temp/DisabledOnTemp.svelte";
</script>

<div class="integrations">

    <div class="nav hds-box">
        <NavLink
            href={consoleUrlWithBlog('/integrations/hyvor-talk')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/hyvor-talk`}
        >
            <img src={hyvorTalkLogo} alt="Hyvor Talk" slot="start" />
            Hyvor Talk
        </NavLink>
    </div>

    <div class="content hds-box">
        <DisabledOnTemp>
            <slot />
        </DisabledOnTemp>
    </div>

</div>

<style>
    .integrations {
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