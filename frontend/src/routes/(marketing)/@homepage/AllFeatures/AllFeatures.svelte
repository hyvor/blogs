<script lang="ts">
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
	import IconCaretDown from '@hyvor/icons/IconCaretDown';

	import Feature from './Feature.svelte';

	type FeatureColor = 'green' | 'blue' | 'orange' | 'red';

	interface FeatureItem {
		icon: any;
		title: string;
		description: string;
	}

	interface Category {
		label: string;
		color: FeatureColor;
		features: FeatureItem[];
	}

	const categories: Category[] = [
		{
			label: 'Post Editor',
			color: 'green',
			features: [
				{ icon: IconPencil, title: 'All the basics', description: 'Bold, italic, headings, lists, quotes, links, and more.' },
				{ icon: IconImage, title: 'Images', description: 'Upload, paste, drag and drop, Unsplash, Excalidraw, and more ways to add images.' },
				{ icon: IconCode, title: 'Embeds', description: 'Easily embed from Youtube, X, Facebook, Instagram, and 1000+ other platforms.' },
				{ icon: IconRegex, title: 'Syntax Highlighting', description: 'Add code blocks with syntax highlighting for 100+ languages.' },
				{ icon: IconMarkdown, title: 'Markdown-friendly', description: 'Markdown shortcuts for all the formatting you need.' },
				{ icon: IconHourglass, title: 'Drafts & Scheduling', description: 'Save drafts and schedule posts to be published in the future.' },
			]
		},
		{
			label: 'Your Blog',
			color: 'blue',
			features: [
				{ icon: IconTag, title: 'Tags', description: 'Organize your posts with tags. Tag index pages are generated automatically.' },
				{ icon: IconPeople, title: 'Authors', description: 'Add one or more authors to your blog posts. Author index pages are generated automatically.' },
				{ icon: IconSignpost2, title: 'Navigation', description: 'Add navigation links to your blog header and footer without writing any code.' },
				{ icon: IconSignTurnSlightRight, title: 'Redirects', description: 'Set up redirects for your old blog posts to keep your SEO juice.' },
				{ icon: IconRss, title: 'Atom (RSS) Feed', description: 'Atom feeds are generated automatically. No extra work needed.' },
				{ icon: IconDiagram3, title: 'Sitemap', description: 'Same for the sitemap! It\'s generated automatically.' },
				{ icon: IconBraces, title: 'Custom Code', description: 'Add custom code to the whole blog or a specific page to integrate with third-party services.' },
			]
		},
		{
			label: 'Optimizations',
			color: 'orange',
			features: [
				{ icon: IconSearchHeart, title: 'SEO', description: 'Meta tags, Open Graph tags, Canonical URLs, and more SEO optimizations.' },
				{ icon: IconLightning, title: 'Speed', description: 'All official themes are optimized for speed. We use caching extensively to make your blog blazing fast.' },
				{ icon: IconArrowsAngleContract, title: 'Image Optimization', description: 'Automatic webp conversion and responsive images for all your images.' },
			]
		},
		{
			label: 'Developers',
			color: 'red',
			features: [
				{ icon: IconDatabase, title: 'Data API', description: 'A REST API to access your blog data, allowing you to use Hyvor Blogs as a headless CMS.' },
				{ icon: IconController, title: 'Console API', description: 'Everything you can do from the Console, you can do with the Console API.' },
				{ icon: IconSend, title: 'Delivery API', description: 'An API to learn how to "serve" your blog. Used for subdirectory hosting.' },
				{ icon: IconSend, title: 'Webhooks', description: 'Get notified when an event happens in your blog.' },
			]
		},
	];

	const colorMap: Record<FeatureColor, string> = {
		green: 'var(--green)',
		blue: 'var(--blue)',
		orange: 'var(--orange)',
		red: 'var(--red)',
	};

	let openIndex = $state(0);

	function toggle(i: number) {
		openIndex = openIndex === i ? -1 : i;
	}
</script>

<section class="all-features hds-container">
	<div class="section-header">
		<h2>Everything you need to blog</h2>
		<p>A complete feature set so you never have to stitch together separate tools.</p>
	</div>

	<div class="accordion">
		{#each categories as cat, i}
			<div class="accordion-item" class:open={openIndex === i}>
				<button
					class="accordion-trigger"
					id="accordion-trigger-{i}"
					onclick={() => toggle(i)}
					aria-expanded={openIndex === i}
					aria-controls="accordion-panel-{i}"
					style="--cat-color: {colorMap[cat.color]}"
				>
					<span class="cat-dot" style="background: {colorMap[cat.color]}"></span>
					<span class="cat-label">{cat.label}</span>
					<span class="cat-count">{cat.features.length} features</span>
					<span class="chevron" class:rotated={openIndex === i}>
						<IconCaretDown size={16} />
					</span>
				</button>

				<div
					class="accordion-body"
					class:open={openIndex === i}
					role="region"
					id="accordion-panel-{i}"
					aria-labelledby="accordion-trigger-{i}"
				>
					<div class="accordion-body-inner">
						<div class="accordion-content">
							<div class="features-grid">
								{#each cat.features as feat}
									<Feature icon={feat.icon} title={feat.title} description={feat.description} color={cat.color} />
								{/each}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/each}
	</div>
</section>

<style lang="scss">
	.all-features {
		padding: 80px 0;
	}

	.section-header {
		text-align: center;
		margin-bottom: 48px;

		h2 {
			font-size: 32px;
			font-weight: 700;
			margin: 0 0 12px;
		}
		p {
			font-size: 1rem;
			color: var(--text-light);
			margin: 0;
		}
	}

	.accordion {
		border: 1px solid var(--border);
		border-radius: 20px;
		overflow: hidden;
	}

	.accordion-item {
		border-bottom: 1px solid var(--border);
		&:last-child {
			border-bottom: none;
		}
	}

	.accordion-trigger {
		width: 100%;
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 20px 24px;
		background: none;
		border: none;
		cursor: pointer;
		text-align: left;
		transition: background 0.15s;
		color: var(--text);

		&:hover {
			background: var(--hover);
		}
	}

	.open .accordion-trigger {
		background: var(--background-2, var(--box-background));
	}

	.cat-dot {
		display: block;
		width: 10px;
		height: 10px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	.cat-label {
		font-size: 16px;
		font-weight: 600;
		flex: 1;
	}

	.cat-count {
		font-size: 13px;
		color: var(--text-light);
	}

	.chevron {
		display: flex;
		align-items: center;
		color: var(--text-light);
		transition: transform 0.2s;
		&.rotated {
			transform: rotate(180deg);
		}
	}

	/* CSS grid row trick: content stays in DOM for SEO, height animates via grid */
	.accordion-body {
		display: grid;
		grid-template-rows: 0fr;
		transition: grid-template-rows 0.22s ease;

		&.open {
			grid-template-rows: 1fr;
		}
	}

	.accordion-body-inner {
		overflow: hidden;
	}

	.accordion-content {
		padding: 24px;
		border-top: 1px solid var(--border);
		background: var(--background);
	}

	.features-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 28px 20px;
	}

	/* Override Feature.svelte width since we're using grid now */
	.features-grid :global(.feature) {
		width: auto;
	}

	@media (max-width: 768px) {
		.features-grid {
			grid-template-columns: 1fr;
		}
	}
</style>