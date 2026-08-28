<script lang="ts">
	import { Footer, FooterLinkList } from '@hyvor/design/marketing';

	const columns = [
		{
			title: 'Product',
			links: [
				{ href: '/console', label: 'Console' },
				{ href: '/themes', label: 'Themes' },
				{ href: '/pricing', label: 'Pricing' },
				{ href: '/docs', label: 'Docs' },
				{ href: '/hosting', label: 'Hosting' }
			]
		},
		{
			title: 'Legal',
			links: [
				{ href: '/terms', label: 'Terms of Service' },
				{ href: '/privacy', label: 'Privacy Policy' },
				{ href: 'https://hyvor.com/compliance', label: 'Compliance', external: true }
			]
		},
		{
			title: 'HYVOR',
			links: [
				{ href: 'https://hyvor.com', label: 'hyvor.com', external: true },
				{ href: 'https://hyvor.com/#letter', label: 'About', external: true },
				{ href: 'https://hyvor.com/security', label: 'Security', external: true },
				{ href: 'https://status.hyvor.com', label: 'System Status', external: true }
			]
		},
		{
			title: 'Alternatives',
			links: [
				{
					href: 'https://hyvor.com/compare/blogs/wordpress',
					label: 'WordPress Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/ghost',
					label: 'Ghost Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/medium',
					label: 'Medium Alternative',
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/blogger',
					label: 'Blogger Alternative',
					external: true
				}
			]
		}
	];
</script>

<Footer
	name="Hyvor Blogs"
	logo="/logo.svg"
	card
	background="#574443"
	email="blogs.support@hyvor.com"
	social={{
		// `Socials` pins each key to HYVOR's own default URL as a literal type,
		// so overriding it for our repo needs a cast
		github: 'https://github.com/hyvor/blogs' as any
	}}
	languageToggle={false}
	max
>
	{#snippet center()}
		<div class="columns">
			{#each columns as col}
				<FooterLinkList title={col.title}>
					{#each col.links as link}
						<a href={link.href} target={link.external ? '_blank' : undefined}>{link.label}</a>
					{/each}
				</FooterLinkList>
			{/each}
		</div>
	{/snippet}
</Footer>

<style>
	.columns {
		display: flex;
		flex-wrap: wrap;
		gap: 40px;
	}

	/* the design system's Footer reserves 100px above itself for the mascot
	   (.footer-outer's own margin-top) — on this page the preceding section
	   already has generous bottom padding for that overlap, so the reserved
	   space just shows up as a blank gap before the footer. Zero it out.
	   !important because that class is compiled with a Svelte scoping class
	   we can't otherwise out-specify */
	:global(.footer-outer) {
		margin-top: 0 !important;
	}
</style>
