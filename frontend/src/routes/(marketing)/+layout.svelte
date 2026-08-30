<script lang="ts">
	import Footer from './Footer.svelte';
	import Header from './Header.svelte';
	import { MARKETING_PAGE_META } from './marketing';
	import { page } from '$app/state';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	const pathname = $derived.by(() => {
		const p = page.url.pathname;
		return p === '/' ? '' : p;
	});

	const RICH_SCHEMA = {
		'@context': 'https://schema.org',
		'@graph': [
			{
				'@type': 'Organization',
				'@id': 'https://hyvor.com/#organization',
				name: 'HYVOR',
				url: 'https://hyvor.com',
				logo: 'https://hyvor.com/api/public/logo/core.svg',
				sameAs: [
					'https://x.com/HyvorHQ',
					'https://github.com/hyvor',
					'https://discord.com/invite/2WRJxQB',
					'https://www.linkedin.com/company/30240435',
					'https://www.youtube.com/@HYVOR',
					'https://bsky.app/profile/hyvor.com'
				]
			},
			{
				'@type': 'WebSite',
				'@id': 'https://blogs.hyvor.com/#website',
				url: 'https://blogs.hyvor.com',
				name: 'Hyvor Blogs',
				publisher: { '@id': 'https://hyvor.com/#organization' }
			},
			{
				'@type': 'SoftwareApplication',
				'@id': 'https://blogs.hyvor.com/#software',
				name: 'Hyvor Blogs',
				applicationCategory: 'WebApplication',
				operatingSystem: 'Web',
				url: 'https://blogs.hyvor.com',
				description: MARKETING_PAGE_META.description,
				offers: {
					'@type': 'Offer',
					url: 'https://blogs.hyvor.com/pricing'
				}
				// TODO: research this (whether to include or not)
				// "aggregateRating": {
				// 	"@type": "AggregateRating",
				// 	"ratingValue": "5",
				// 	"reviewCount": "2",
				// 	"bestRating": "5",
				// 	"worstRating": "1"
				// },
				// "review": [
				// 	{
				// 		"@type": "Review",
				// 		"reviewRating": {
				// 			"@type": "Rating",
				// 			"ratingValue": "5",
				// 			"bestRating": "5"
				// 		},
				// 		"author": {
				// 			"@type": "Person",
				// 			"name": "Lionel S."
				// 		},
				// 		"reviewBody": "I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully."
				// 	},
				// 	{
				// 		"@type": "Review",
				// 		"reviewRating": {
				// 			"@type": "Rating",
				// 			"ratingValue": "5",
				// 			"bestRating": "5"
				// 		},
				// 		"author": {
				// 			"@type": "Person",
				// 			"name": "Manoj P."
				// 		},
				// 		"reviewBody": "The platform offers a seamless and user-friendly experience for both bloggers and readers. The customisation options are extensive, allowing us to create a unique and visually appealing blog."
				// 	}
				// ]
			}
		]
	};

	const RICH_SCHEMA_SCRIPT =
		'<' + 'script type="application/ld+json">' + JSON.stringify(RICH_SCHEMA) + '<' + '/script>';
</script>

<svelte:head>
	<meta property="og:image" content={MARKETING_PAGE_META.ogImage} />
	<meta property="og:url" content={MARKETING_PAGE_META.urlBasePath + pathname} />
	<link rel="canonical" href={MARKETING_PAGE_META.urlBasePath + pathname} />

	<meta name="twitter:card" content="summary_large_image" />

	{@html RICH_SCHEMA_SCRIPT}
</svelte:head>

<Header />

{@render children?.()}

{#if page.url.pathname !== '/themes'}
	<Footer />
{/if}

<style>
	@media (max-width: 600px) {
		:global(.hds-container),
		:global(.hds-container-max) {
			padding-left: 20px;
			padding-right: 20px;
		}

		:global(header .container),
		:global(footer .container) {
			padding-left: 20px !important;
			padding-right: 20px !important;
		}
	}
</style>
