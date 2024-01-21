<script>
	import { Button, Link, Loader } from "@hyvor/design/components";
    import ThemesPreview from "../@components/ThemesPreview.svelte";
	import { IconBoxArrowUpRight } from "@hyvor/icons";
	import FeatureSectionTitle from "./FeatureSectionTitle.svelte";

    let isLoading = true;
</script>

<FeatureSectionTitle 
    title="Custom Themes" 
    subtitle="Match your blog to your brand with custom themes. Choose from a variety of themes or create your own."
/>

<div class="preview">
    <ThemesPreview on:load={() => isLoading = false} />

    {#if isLoading}
        <Loader full />
    {/if}
</div>

<div class="action">
    <Button as="a" href="/themes" color="gray">
        View Pre-built Themes &rarr;
    </Button>
</div>

<div class="dev">
    Are you familiar with HTML and CSS? Have a web developer in your team? You can create your own theme from scratch! 
    <Link href="https://blogs.hyvor.com/docs/themes-overview" target="_blank">
        Learn more
        <IconBoxArrowUpRight slot="end" size={12} />
    </Link>
</div>

<style>
    .preview {
        margin-top: 50px;
        padding: 0 100px;
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
</style>