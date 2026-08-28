<script lang="ts">
	import { Dropdown, Button } from '@hyvor/design/components';
	import { Header, HeaderNavLink, HeaderLanguageToggle } from '@hyvor/design/marketing';
	import { page } from '$app/stores';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconPalette from '@hyvor/icons/IconPalette';
	import IconPuzzle from '@hyvor/icons/IconPuzzle';
	import {
		LANGUAGES_CONFIG,
		DEFAULT_MARKETING_LANGUAGE,
		buildMarketingUrl,
		getStaticString
	} from './[[lang]]/marketingLang';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';

	let resourcesDropdown = $state(false);

	const currentLang = $derived(
		LANGUAGES_CONFIG.find((lang) => lang.code === $page.url.pathname.split('/')[1]) ??
			LANGUAGES_CONFIG.find((lang) => lang.default)!
	);

	// see getStaticString's own comment: Header can't use the "i18n" context,
	// so this looks the current language's strings up directly instead
	function t(key: string) {
		return getStaticString(currentLang.strings, `nav.header.${key}`);
	}

	// re-prefix a plain, language-agnostic path (e.g. "/pricing") with the
	// current language, so in-site nav links stay on the same language
	function localizedHref(path: string) {
		return buildMarketingUrl(path, currentLang.code, currentLang.code) || '/';
	}

	// close a dropdown/menu once a link inside it is clicked
	function closeOnLinkClick(e: MouseEvent, close: () => void) {
		const target = e.target as HTMLElement;
		if (target.tagName === 'A' || target.closest('a')) {
			close();
		}
	}

	// the current pathname with any language prefix stripped, so active-state
	// checks below can compare against plain routes regardless of language
	const unlocalizedPath = $derived(
		buildMarketingUrl($page.url.pathname, currentLang.code, DEFAULT_MARKETING_LANGUAGE) || '/'
	);

	const isThemesOrIntegrations = $derived(
		unlocalizedPath === '/themes' || unlocalizedPath.startsWith('/integrations')
	);
</script>

<Header
	product="blogs"
	name="Hyvor Blogs"
	logo="/logo.svg"
	logoAltText={t('logo')}
	href={localizedHref('/')}
	darkToggle={false}
	max
	menuLabel={t('menu')}
>
	{#snippet center()}
		<HeaderNavLink href={localizedHref('/pricing')} active={unlocalizedPath === '/pricing'}>
			{t('pricing')}
		</HeaderNavLink>
		<HeaderNavLink href={localizedHref('/docs')} active={unlocalizedPath.startsWith('/docs')}>
			{t('docs')}
		</HeaderNavLink>
		<HeaderNavLink href={localizedHref('/hosting')} active={unlocalizedPath.startsWith('/hosting')}>
			{t('hosting')}
		</HeaderNavLink>

		<Dropdown bind:show={resourcesDropdown} align="center" width={320} contentPadding={8}>
			{#snippet trigger()}
				<HeaderNavLink active={isThemesOrIntegrations}>
					{t('resources')}
					<IconChevronDown size={11} />
				</HeaderNavLink>
			{/snippet}
			{#snippet content()}
				<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
				<div
					class="dropdown-menu"
					onclick={(e) => closeOnLinkClick(e, () => (resourcesDropdown = false))}
				>
					<HeaderNavLink
						href={localizedHref('/themes')}
						menu
						active={unlocalizedPath === '/themes'}
					>
						{#snippet start()}<IconPalette size={18} />{/snippet}
						{t('themes.label')}
						{#snippet description()}{t('themes.description')}{/snippet}
					</HeaderNavLink>
					<HeaderNavLink
						href={localizedHref('/integrations')}
						menu
						active={unlocalizedPath.startsWith('/integrations')}
					>
						{#snippet start()}<IconPuzzle size={18} />{/snippet}
						{t('integrations.label')}
						{#snippet description()}{t('integrations.description')}{/snippet}
					</HeaderNavLink>
				</div>
			{/snippet}
		</Dropdown>

		<HeaderNavLink href="https://github.com/hyvor/blogs" target="_blank">
			<IconGithub size={12} />
			Github
			<IconBoxArrowUpRight size={11} />
		</HeaderNavLink>

		<HeaderLanguageToggle
			languages={LANGUAGES_CONFIG}
			current={currentLang.code}
			href={(code) => buildMarketingUrl($page.url.pathname, currentLang.code, code) || '/'}
			label={t('changeLanguage')}
		/>
	{/snippet}

	{#snippet end()}
		<Button href="/console" as="a">{t('goToConsole')}</Button>
	{/snippet}
</Header>

<style>
	.dropdown-menu {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}
</style>
