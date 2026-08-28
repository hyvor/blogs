<script lang="ts">
	import { Dropdown, Button } from '@hyvor/design/components';
	import { Header, HeaderNavLink, HeaderLanguageToggle } from '@hyvor/design/marketing';
	import { page } from '$app/stores';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import { LANGUAGES_CONFIG, buildMarketingUrl } from './[[lang]]/marketingLang';

	let resourcesDropdown = $state(false);

	const currentLang = $derived(
		LANGUAGES_CONFIG.find((lang) => lang.code === $page.url.pathname.split('/')[1]) ??
			LANGUAGES_CONFIG.find((lang) => lang.default)!
	);

	// close a dropdown/menu once a link inside it is clicked
	function closeOnLinkClick(e: MouseEvent, close: () => void) {
		const target = e.target as HTMLElement;
		if (target.tagName === 'A' || target.closest('a')) {
			close();
		}
	}

	const isThemesOrIntegrations = $derived(
		$page.url.pathname === '/themes' || $page.url.pathname.startsWith('/integrations')
	);
</script>

<Header product="blogs" name="Hyvor Blogs" logo="/logo.svg" darkToggle={false} max>
	{#snippet center()}
		<HeaderNavLink href="/pricing" active={$page.url.pathname === '/pricing'}>
			Pricing
		</HeaderNavLink>
		<HeaderNavLink href="/docs" active={$page.url.pathname.startsWith('/docs')}>Docs</HeaderNavLink>
		<HeaderNavLink href="/hosting" active={$page.url.pathname.startsWith('/hosting')}>
			Hosting
		</HeaderNavLink>

		<Dropdown bind:show={resourcesDropdown} align="center" contentPadding={8}>
			{#snippet trigger()}
				<HeaderNavLink active={isThemesOrIntegrations}>
					Resources
					<IconCaretDown size={11} />
				</HeaderNavLink>
			{/snippet}
			{#snippet content()}
				<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
				<div
					class="dropdown-menu"
					onclick={(e) => closeOnLinkClick(e, () => (resourcesDropdown = false))}
				>
					<HeaderNavLink href="/themes" menu active={$page.url.pathname === '/themes'}>
						Themes
					</HeaderNavLink>
					<HeaderNavLink
						href="/integrations"
						menu
						active={$page.url.pathname.startsWith('/integrations')}
					>
						Integrations
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
		/>
	{/snippet}

	{#snippet end()}
		<Button href="/console" as="a">Go to Console &rarr;</Button>
	{/snippet}
</Header>

<style>
	.dropdown-menu {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}
</style>
