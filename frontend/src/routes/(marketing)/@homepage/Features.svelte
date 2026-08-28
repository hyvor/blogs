<script lang="ts">
	import { FeatureSplit } from '@hyvor/design/marketing';
	import ThemesLivePreview from './ThemesLivePreview.svelte';
	import SeoMockup from './SeoMockup.svelte';
	import HostingMockup from './HostingMockup.svelte';
	import MultiLanguageMockup from './MultiLanguageMockup.svelte';
	import SuiteMockup from './SuiteMockup.svelte';
	import OwnershipMockup from './OwnershipMockup.svelte';
	import AllInOneMockup from './AllInOneMockup.svelte';
	import AiMockup from './AiMockup.svelte';
	import LinkAnalyzerMockup from './LinkAnalyzerMockup.svelte';
</script>

<FeatureSplit
	eyebrow="Own Your Content"
	title="Your content. Your rules."
	description="Hyvor Blogs is open-source and built so you're never locked in. Export everything whenever you want, or self-host on your own infrastructure for full control and privacy."
	bullets={[
		'One-click data export in JSON',
		'AGPL-3.0 licensed - switch to self-hosting anytime',
		'No ads, no third-party trackers on your blog'
	]}
	altBg
>
	{#snippet visual()}
		<OwnershipMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="All-in-one"
	title="No plugins.<br/>No maintenance.<br/> No nonsense."
	description="Everything you need to run a blog is already built in. No plugins to hunt down, no upgrades to babysit, no surprise charges from third-party add-ons - just one platform that works."
	bullets={[
		'Every feature included, no plugins required',
		'We handle upgrades, security patches, etc. for you',
		'No extra charges or third-party add-on costs'
	]}
	flip
>
	{#snippet visual()}
		<AllInOneMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Custom Themes"
	title="Your Style, Your Way"
	description="Choose from a library of beautiful themes or build your own with plain HTML and CSS. Every color, font, and layout detail is yours to control."
	bullets={[
		'Fully open-source themes, free to use and modify',
		'Theme development docs & API',
		'or, set up headless mode with your own frontend'
	]}
	button={[
		{ href: '/themes', label: 'Browse themes', external: true },
		{ href: '/docs/headless', label: 'Headless docs' }
	]}
	altBg
	overlap
>
	{#snippet visual()}
		<ThemesLivePreview />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="SEO & Speed"
	title="Zero-config SEO.<br/>Lightning-fast pages."
	description="Every blog is automatically optimised for search engines and page speed. You write; we handle the technical SEO so your content ranks."
	bullets={[
		'Meta tags, Open Graph, Twitter Cards',
		'Auto-generated sitemaps & robots.txt',
		'In-built SEO & link analyzers',
		'Static HTML with zero JS bloat',
		'Automatic WebP image conversion'
	]}
	flip
>
	{#snippet visual()}
		<SeoMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Flexible Hosting"
	title="Custom domain, Sub-directory, Headless"
	description="Move to a custom domain in seconds, serve your blog from inside your existing site with a sub-directory, or go fully headless with your own frontend. Your call."
	bullets={[
		'Free TLS certificates on all custom domains',
		'Cloudflare Workers, Docker, Next.js, Laravel…',
		'Reverse proxy support for sub-directory'
	]}
	altBg
>
	{#snippet visual()}
		<HostingMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Comments & Newsletters"
	title="Talk & Post included"
	description="Every Hyvor Blogs plan comes with Hyvor Talk for comments and Hyvor Post for newsletters, completely free. No third-party embeds, no extra subscriptions — one account, one dashboard, everything connected."
	bullets={[
		'Hyvor Talk comments, free on every plan',
		'Hyvor Post newsletters, free on every plan',
		'One account and dashboard for all three'
	]}
	button={{ href: '/pricing', label: "See what's included" }}
	flip
>
	{#snippet visual()}
		<SuiteMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="AI Features"
	title="AI when you need it"
	description="Save time and effort with AI-powered features. Generate content, translate posts, and ask for suggestions - all without leaving your blog dashboard."
	bullets={[
		'AI content generation',
		'Improvement suggestions',
		'Bulk post editing (e.g. add internal links)'
	]}
	interactiveBullets
	altBg
>
	{#snippet visual(active)}
		<AiMockup {active} />
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
>
	{#snippet visual()}
		<MultiLanguageMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow="Link Analyzer"
	title="No broken links"
	description="Hyvor Blogs automatically fetches and checks every link in your posts on a recurring schedule, flagging broken links and redirects before your readers ever hit them."
	bullets={[
		'Automatic, periodic checks for every link',
		'Per-post and full-blog link reports',
		'Instant flags for broken links, redirects & status codes'
	]}
>
	{#snippet visual()}
		<LinkAnalyzerMockup />
	{/snippet}
</FeatureSplit>
