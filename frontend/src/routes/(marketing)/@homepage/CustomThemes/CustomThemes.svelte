<script>
	import { Button, Link, Loader } from "@hyvor/design/components";
    import ThemesPreview from "../../@components/ThemesPreview.svelte";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
    import logo from "./custom-themes.svg";

    let isLoading = true;
</script>

<FeatureSectionTitle 
    title="Custom Themes" 
    subtitle="Match your blog to your brand with custom themes. Choose from a variety of themes or create your own."
    h2Style="margin-top: 50px;"
    icon={logo}
/>

<div class="preview hds-container">
    <ThemesPreview 
        on:load={() => isLoading = false}
        lockScroll={true}
    />

    {#if isLoading}
        <Loader full />
    {/if}
</div>

<div class="action">
    <Button as="a" href="/themes" color="gray">
        Go to themes page &rarr;
    </Button>
</div>

<div class="dev">
    Are you familiar with HTML and CSS? Have a web developer in your team? You can create your own theme from scratch! 
    <Link href="https://blogs.hyvor.com/docs/themes-overview" target="_blank">
        Learn more
        <IconBoxArrowUpRight slot="end" size={12} />
    </Link>
</div>

<div class="dev">
    <h2>OR</h2>
</div>

<div class="dev-theme">
    We will develop a theme for you at your request for your requirements.
    <!-- <Link href="/theme-service" target="_blank">
        Learn more
        <IconBoxArrowUpRight slot="end" size={12} />
    </Link> -->
    <div class="themes-dev">
        <Button as="a" href="/theme-service" variant="fill" size="medium" color="gray">
            Request a Theme Now &rarr;
        </Button>
    </div>
</div>

<style lang="scss">
    .preview {
        margin-top: 50px;
        width: 1300px;
        height: 600px;
    }
    .action {
        margin-top: 50px;
        margin-bottom: 30px;
        text-align: center;
    }

    .dev {
        text-align: center;
        color: var(--text-light);
        width: 500px;
        max-width: 100%;
        margin: auto;
    }

    .dev-theme {
        text-align: center;
        color: var(--text-light);
        width: 500px;
        max-width: 100%;
        margin: auto;
        display: flex;
        flex-direction: column;
        
        .themes-dev {
            margin-top: 20px;
        }
    }
</style>