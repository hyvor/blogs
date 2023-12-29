<script>
	import { NavLink } from "@hyvor/design/components";
	import { blogStore } from "../../lib/stores";
	import { page } from "$app/stores";
    
    import hyvorTalkLogo from '$lib/img/services/hyvor-talk.svg';

</script>

<div class="integrations">

    <div class="nav hds-box">
        <NavLink 
            href={`/console/${$blogStore.subdomain}/integrations/hyvor-talk`}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/hyvor-talk`}
        >
            <img src={hyvorTalkLogo} alt="Hyvor Talk" slot="start" />
            Hyvor Talk
        </NavLink>
    </div>

    <div class="content hds-box">
        <slot />
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
    }
</style>