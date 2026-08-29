<script lang="ts">
	import { Footer, FooterLinkList } from '@hyvor/design/marketing';
	import { page } from '$app/stores';
	import { LANGUAGES_CONFIG, buildMarketingUrl, getStaticString } from './[[lang]]/marketingLang';

	const currentLang = $derived(
		LANGUAGES_CONFIG.find((lang) => lang.code === $page.url.pathname.split('/')[1]) ??
			LANGUAGES_CONFIG.find((lang) => lang.default)!
	);

	function t(key: string) {
		return getStaticString(currentLang.strings, key);
	}

	function localizedHref(path: string) {
		return buildMarketingUrl(path, currentLang.code, currentLang.code) || '/';
	}

	interface FooterLink {
		href: string;
		label: string;
		external?: boolean;
		localize?: boolean;
	}

	const columns = $derived.by<{ title: string; links: FooterLink[] }[]>(() => [
		{
			title: t('nav.footer.columns.product'),
			links: [
				{ href: '/console', label: t('nav.footer.console') },
				{ href: '/themes', label: t('nav.header.themes.label'), localize: true },
				{ href: '/pricing', label: t('nav.header.pricing'), localize: true },
				{ href: '/docs', label: t('nav.header.docs'), localize: true },
				{ href: '/hosting', label: t('nav.header.hosting'), localize: true }
			]
		},
		{
			title: t('nav.footer.columns.legal'),
			links: [
				{ href: '/terms', label: t('nav.footer.termsOfService') },
				{ href: '/privacy', label: t('nav.footer.privacyPolicy') },
				{ href: 'https://hyvor.com/compliance', label: t('nav.footer.compliance'), external: true }
			]
		},
		{
			title: 'HYVOR',
			links: [
				{ href: 'https://hyvor.com', label: 'hyvor.com', external: true },
				{ href: 'https://hyvor.com/#letter', label: t('nav.footer.about'), external: true },
				{ href: 'https://hyvor.com/security', label: t('nav.footer.security'), external: true },
				{ href: 'https://status.hyvor.com', label: t('nav.footer.systemStatus'), external: true }
			]
		},
		{
			title: t('nav.footer.columns.alternatives'),
			links: [
				{
					href: 'https://hyvor.com/compare/blogs/wordpress',
					label: t('nav.footer.wordpressAlternative'),
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/ghost',
					label: t('nav.footer.ghostAlternative'),
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/medium',
					label: t('nav.footer.mediumAlternative'),
					external: true
				},
				{
					href: 'https://hyvor.com/compare/blogs/blogger',
					label: t('nav.footer.bloggerAlternative'),
					external: true
				}
			]
		}
	]);
</script>

<Footer
	name="Hyvor Blogs"
	logo="/logo.svg"
	logoAltText={t('nav.header.logo')}
	card
	background="#574443"
	email="blogs.support@hyvor.com"
	social={{
		github: 'https://github.com/hyvor/blogs' as any
	}}
	languageToggle={false}
	max
	copyEmailLabel={t('nav.footer.copyEmail')}
	copiedLabel={t('nav.footer.copied')}
	gdprText={t('nav.footer.gdprCompliant')}
	fromFranceText={t('nav.footer.fromFrance')}
>
	{#each columns as col}
		<FooterLinkList title={col.title}>
			{#each col.links as link}
				<a
					href={link.localize ? localizedHref(link.href) : link.href}
					target={link.external ? '_blank' : undefined}
				>
					{link.label}
				</a>
			{/each}
		</FooterLinkList>
	{/each}
</Footer>

<style>
	:global(.footer-outer) {
		margin-top: 0 !important;
	}
</style>
