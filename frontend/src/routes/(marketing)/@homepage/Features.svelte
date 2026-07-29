<script lang="ts">
	import FeatureSplit from './FeatureSplit.svelte';
	import ThemesMockup from './ThemesMockup.svelte';
	import ThemesLivePreview from './ThemesLivePreview.svelte';
	import SeoMockup from './SeoMockup.svelte';
	import HostingMockup from './HostingMockup.svelte';
	import MultiLanguageMockup from './MultiLanguageMockup.svelte';
	import SuiteMockup from './SuiteMockup.svelte';
</script>

<FeatureSplit
	eyebrow="Custom Themes"
	title="Your blog, your brand"
	description="Choose from a library of beautiful themes or build your own with plain HTML and CSS. Every colour, font, and layout detail is yours to control."
	bullets={[
		'Original & ported themes included',
		'Open-source, fork and customise freely',
		'Theme development docs & API'
	]}
	button={{ href: '/themes', label: 'Browse themes' }}
>
	{#snippet visual()}
		<ThemesMockup />
	{/snippet}
	{#snippet after()}
		<ThemesLivePreview />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="SEO & Speed"
	title="Perfect scores, zero configuration"
	description="Every blog is automatically optimised for search engines and page speed. You write; we handle the technical SEO so your content ranks."
	bullets={[
		'Meta tags, Open Graph, Twitter Cards',
		'Auto-generated sitemaps & robots.txt',
		'In-built SEO & link analyzers',
		'Static HTML with zero JS bloat',
		'Automatic WebP image conversion'
	]}
	flip
	altBg
>
	{#snippet visual()}
		<SeoMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Flexible Hosting"
	title="Host it anywhere you like"
	description="Start on a subdomain in seconds, move to a custom domain, or serve your blog from inside your existing site with a sub-directory. Your call."
	bullets={[
		'Free SSL on all custom domains',
		'Cloudflare Workers, Docker, Next.js, Laravel…',
		'Reverse proxy support for sub-directory'
	]}
>
	{#snippet visual()}
		<HostingMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Multi-language"
	title="Reach a global audience"
	description="Translate posts, tags, author names and everything else. Add languages in one click and Hyvor Blogs handles routing, hreflang tags, and RTL layouts automatically."
	bullets={[
		'RTL language support built-in',
		'Integrated AI translator',
		'Automatic hreflang & i18n routing'
	]}
	flip
	altBg
>
	{#snippet visual()}
		<MultiLanguageMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Content & Community"
	title="Blogging, comments, and newsletters — all in one place"
	description="Every Hyvor Blogs plan comes with Hyvor Talk for comments and Hyvor Post for newsletters, completely free. No third-party embeds, no extra subscriptions — one account, one dashboard, everything connected."
	bullets={[
		'Hyvor Talk comments, free on every plan',
		'Hyvor Post newsletters, free on every plan',
		'One account and dashboard for all three'
	]}
	button={{ href: '/pricing', label: "See what's included" }}
>
	{#snippet visual()}
		<SuiteMockup />
	{/snippet}
</FeatureSplit>
