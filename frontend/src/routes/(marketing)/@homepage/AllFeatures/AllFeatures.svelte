<script>
	import IconArrowsAngleContract from '@hyvor/icons/IconArrowsAngleContract';
	import IconBraces from '@hyvor/icons/IconBraces';
	import IconCode from '@hyvor/icons/IconCode';
	import IconController from '@hyvor/icons/IconController';
	import IconDatabase from '@hyvor/icons/IconDatabase';
	import IconDiagram3 from '@hyvor/icons/IconDiagram3';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconImage from '@hyvor/icons/IconImage';
	import IconLightning from '@hyvor/icons/IconLightning';
	import IconMarkdown from '@hyvor/icons/IconMarkdown';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconPeople from '@hyvor/icons/IconPeople';
	import IconRegex from '@hyvor/icons/IconRegex';
	import IconRss from '@hyvor/icons/IconRss';
	import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
	import IconSend from '@hyvor/icons/IconSend';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';
	import IconSignpost2 from '@hyvor/icons/IconSignpost2';
	import IconTag from '@hyvor/icons/IconTag';

	import FeatureSectionTitle from '../FeatureSectionTitle.svelte';
	import Feature from './Feature.svelte';
	import FeatureSection from './FeatureSection.svelte';
</script>

<FeatureSectionTitle
	title="And many more features..."
	subtitle="All the features you need to create a successful blog."
/>

<div class="hds-container wrap">
	<FeatureSection color="green">Post Editor</FeatureSection>

	<div class="features-wrap">
		<Feature
			icon={IconPencil}
			title="All the basics"
			description="Bold, italic, headings, lists, quotes, links, and more."
			color="green"
		/>

		<Feature
			icon={IconImage}
			title="Images"
			description="Upload, paste, drag and drop, Unsplash, Excalidraw, and more ways to add images."
			color="green"
		/>

		<Feature
			icon={IconCode}
			title="Embeds"
			description="Easily embed from Youtube, X, Facebook, Instagram, and 1000+ other platforms."
			color="green"
		/>

		<Feature
			icon={IconRegex}
			title="Syntax Highlighting"
			description="Add code blocks with syntax highlighting for 100+ languages."
			color="green"
		/>

		<Feature
			icon={IconMarkdown}
			title="Markdown-friendly"
			description="Markdown shortcuts for all the formatting you need."
			color="green"
		/>

		<Feature
			icon={IconHourglass}
			title="Drafts & Scheduling"
			description="Save drafts and schedule posts to be published in the future."
			color="green"
		/>
	</div>

	<FeatureSection color="blue">Your Blog</FeatureSection>

	<div class="features-wrap">
		<Feature
			icon={IconTag}
			title="Tags"
			description="Organize your posts with tags. Tag index pages are generated automatically."
			color="blue"
		/>

		<Feature
			icon={IconPeople}
			title="Authors"
			description="Add one or more authors to your blog posts. Author index pages are generated automatically."
			color="blue"
		/>

		<Feature
			icon={IconSignpost2}
			title="Navigation"
			description="Add navigation links to your blog header and footer without writing any code."
			color="blue"
		/>

		<Feature
			icon={IconSignTurnSlightRight}
			title="Redirects"
			description="Set up redirects for your old blog posts to keep your SEO juice."
			color="blue"
		/>

		<Feature
			icon={IconRss}
			title="Atom (RSS) Feed"
			description="Atom feeds are generated automatically. No extra work needed."
			color="blue"
		/>

		<Feature
			icon={IconDiagram3}
			title="Sitemap"
			description="Same for the sitemap! It's generated automatically."
			color="blue"
		/>

		<Feature
			icon={IconBraces}
			title="Custom Code"
			description="Add custom code to the whole blog or a specific page to integrate with third-party services."
			color="blue"
		/>
	</div>

	<FeatureSection color="orange">Optimizations</FeatureSection>

	<div class="features-wrap">
		<Feature
			icon={IconSearchHeart}
			title="SEO"
			description="Meta tags, Open Graph tags, Canonical URLs, and more SEO optimizations."
			color="orange"
		/>
		<Feature
			icon={IconLightning}
			title="Speed"
			description="All official themes are optimized for speed. We use caching extensively to make your blog blazing fast."
			color="orange"
		/>
		<Feature
			icon={IconArrowsAngleContract}
			title="Image Optimization"
			description="Automatic webp conversion and responsive images for all your images."
			color="orange"
		/>
	</div>

	<FeatureSection color="red">Developers</FeatureSection>

	<div class="features-wrap">
		<Feature
			icon={IconDatabase}
			title="Data API"
			description="A REST API to access your blog data, allowing you to use Hyvor Blogs as a headless CMS."
			color="red"
		/>
		<Feature
			icon={IconController}
			title="Console API"
			description="Everything you can do from the Console, you can do with the Console API."
			color="red"
		/>
		<Feature
			icon={IconSend}
			title="Delivery API"
			description={`An API to learn how to "serve" your blog. Used for subdirectory hosting.`}
			color="red"
		/>
		<Feature
			icon={IconSend}
			title="Webhooks"
			description="Get notified when an event happens in your blog."
			color="red"
		/>
	</div>
</div>

<style>
	.wrap {
		margin: 60px auto;
	}
	.features-wrap {
		display: flex;
		gap: 30px 15px;
		flex-wrap: wrap;
	}

	@media (max-width: 992px) {
		.features-wrap {
			flex-direction: column;
		}
	}
</style>
