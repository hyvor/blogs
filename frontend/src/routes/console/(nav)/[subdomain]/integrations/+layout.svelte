<script lang="ts">
	import { NavLink } from "@hyvor/design/components";
    import hyvorTalkLogo from '$lib/img/services/hyvor-talk.svg';
    import simpleAnalyticsLogo from '$lib/img/services/simple-analytics.svg';
	import { page } from "$app/stores";
	import { blogStore } from "../../../lib/stores/blogStore";
	import { consoleUrl, consoleUrlWithBlog } from "../../../lib/consoleUrl";
	import DisabledOnTemp from "../Temp/DisabledOnTemp.svelte";
    interface Props {
        children?: import('svelte').Snippet;
    }

    let { children }: Props = $props();
</script>

<div class="integrations">

    <div class="nav hds-box">
        <NavLink
            href={consoleUrlWithBlog('/integrations/hyvor-talk')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/hyvor-talk`}
        >
            {#snippet start()}
                        <img src={hyvorTalkLogo} alt="Hyvor Talk"  />
                    {/snippet}
            Hyvor Talk
        </NavLink>

        <NavLink
            href={consoleUrlWithBlog('/integrations/simple-analytics')}
            active={$page.url.pathname === `/console/${$blogStore.subdomain}/integrations/simple-analytics`}
        >
            {#snippet start()}
                       <img src={simpleAnalyticsLogo} alt="Simple Analytics" />
                    {/snippet}
            Simple Analytics
        </NavLink>
    </div>

    <div class="content hds-box">
        <DisabledOnTemp>
            {@render children?.()}
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
        overflow: auto;
    }

    @media (max-width: 992px) {
        .integrations {
            flex-direction: column;
        }
        .nav {
            width: 100%;
            margin-right: 0;
            margin-bottom: 20px;
        }
    }

</style>
