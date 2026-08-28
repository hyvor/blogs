<script lang="ts">
	import { AllFeaturesAccordion } from '@hyvor/design/marketing';
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

	const categories = [
		{
			label: 'Post Editor',
			icon: IconPencil,
			color: 'var(--green)',
			features: [
				{
					icon: IconPencil,
					title: 'All the basics',
					description: 'Bold, italic, headings, lists, quotes, links, and more.'
				},
				{
					icon: IconImage,
					title: 'Images',
					description:
						'Upload, paste, drag and drop, Unsplash, Excalidraw, and more ways to add images.'
				},
				{
					icon: IconCode,
					title: 'Embeds',
					description:
						'Easily embed from Youtube, X, Facebook, Instagram, and 1000+ other platforms.'
				},
				{
					icon: IconRegex,
					title: 'Syntax Highlighting',
					description: 'Add code blocks with syntax highlighting for 100+ languages.'
				},
				{
					icon: IconMarkdown,
					title: 'Markdown-friendly',
					description: 'Markdown shortcuts for all the formatting you need.'
				},
				{
					icon: IconHourglass,
					title: 'Drafts & Scheduling',
					description: 'Save drafts and schedule posts to be published in the future.'
				}
			]
		},
		{
			label: 'Your Blog',
			icon: IconSignpost2,
			color: 'var(--blue)',
			features: [
				{
					icon: IconTag,
					title: 'Tags',
					description: 'Organize your posts with tags. Tag index pages are generated automatically.'
				},
				{
					icon: IconPeople,
					title: 'Authors',
					description:
						'Add one or more authors to your blog posts. Author index pages are generated automatically.'
				},
				{
					icon: IconSignpost2,
					title: 'Navigation',
					description:
						'Add navigation links to your blog header and footer without writing any code.'
				},
				{
					icon: IconSignTurnSlightRight,
					title: 'Redirects',
					description: 'Set up redirects for your old blog posts to keep your SEO juice.'
				},
				{
					icon: IconRss,
					title: 'Atom (RSS) Feed',
					description: 'Atom feeds are generated automatically. No extra work needed.'
				},
				{
					icon: IconDiagram3,
					title: 'Sitemap',
					description: "Same for the sitemap! It's generated automatically."
				},
				{
					icon: IconBraces,
					title: 'Custom Code',
					description:
						'Add custom code to the whole blog or a specific page to integrate with third-party services.'
				}
			]
		},
		{
			label: 'Optimizations',
			icon: IconLightning,
			color: 'var(--orange)',
			features: [
				{
					icon: IconSearchHeart,
					title: 'SEO',
					description: 'Meta tags, Open Graph tags, Canonical URLs, and more SEO optimizations.'
				},
				{
					icon: IconLightning,
					title: 'Speed',
					description:
						'All official themes are optimized for speed. We use caching extensively to make your blog blazing fast.'
				},
				{
					icon: IconArrowsAngleContract,
					title: 'Image Optimization',
					description: 'Automatic webp conversion and responsive images for all your images.'
				}
			]
		},
		{
			label: 'Developers',
			icon: IconDatabase,
			color: 'var(--red)',
			features: [
				{
					icon: IconDatabase,
					title: 'Data API',
					description:
						'A REST API to access your blog data, allowing you to use Hyvor Blogs as a headless CMS.'
				},
				{
					icon: IconController,
					title: 'Console API',
					description: 'Everything you can do from the Console, you can do with the Console API.'
				},
				{
					icon: IconSend,
					title: 'Delivery API',
					description: 'An API to learn how to "serve" your blog. Used for subdirectory hosting.'
				},
				{
					icon: IconSend,
					title: 'Webhooks',
					description: 'Get notified when an event happens in your blog.'
				}
			]
		}
	];
</script>

<AllFeaturesAccordion
	title="And There's More..."
	description="Everything you need to take your blog from idea to impact."
	{categories}
/>
