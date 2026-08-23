<script lang="ts">
	import { Button, Dropdown } from '@hyvor/design/components';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconGithub from '@hyvor/icons/IconGithub';
	import IconCaretDown from '@hyvor/icons/IconCaretDown';
	import IconList from '@hyvor/icons/IconList';
	import IconX from '@hyvor/icons/IconX';
	import { LANGUAGES_CONFIG, buildMarketingUrl } from './[[lang]]/marketingLang';

	let resourcesDropdown = $state(false);
	let langDropdown = $state(false);
	let mobileOpen = $state(false);
	let scrolled = $state(false);

	const currentLang = $derived(
		LANGUAGES_CONFIG.find((lang) => lang.code === $page.url.pathname.split('/')[1]) ??
			LANGUAGES_CONFIG.find((lang) => lang.default)!
	);

	onMount(() => {
		const onScroll = () => (scrolled = window.scrollY > 8);
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
		return () => window.removeEventListener('scroll', onScroll);
	});

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

<header class="site-header" class:scrolled>
	<div class="hds-container-max header-inner">
		<a class="brand" href="/">
			<img src="/logo.svg" alt="Hyvor Blogs" width="26" height="26" />
			<span>Hyvor Blogs</span>
		</a>

		<nav class="center">
			<a class="nav-link" href="/pricing" class:active={$page.url.pathname === '/pricing'}>
				Pricing
			</a>
			<a class="nav-link" href="/docs" class:active={$page.url.pathname.startsWith('/docs')}>
				Docs
			</a>
			<a
				class="nav-link"
				href="/hosting"
				class:active={$page.url.pathname.startsWith('/hosting')}
			>
				Hosting
			</a>

			<Dropdown bind:show={resourcesDropdown} align="center" contentPadding={8}>
				{#snippet trigger()}
					<span class="nav-link nav-trigger" class:active={isThemesOrIntegrations}>
						Resources
						<IconCaretDown size={11} />
					</span>
				{/snippet}
				{#snippet content()}
					<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
					<div
						class="dropdown-menu"
						onclick={(e) => closeOnLinkClick(e, () => (resourcesDropdown = false))}
					>
						<a
							href="/themes"
							class="dropdown-link"
							class:active={$page.url.pathname === '/themes'}
						>
							Themes
						</a>
						<a
							href="/integrations"
							class="dropdown-link"
							class:active={$page.url.pathname.startsWith('/integrations')}
						>
							Integrations
						</a>
					</div>
				{/snippet}
			</Dropdown>

			<a class="nav-link" href="https://github.com/hyvor/blogs" target="_blank">
				<IconGithub size={12} />
				Github
				<IconBoxArrowUpRight size={11} />
			</a>

			<div class="lang-toggle">
				<Dropdown bind:show={langDropdown} align="center" contentPadding={8}>
					{#snippet trigger()}
						<span class="nav-link nav-trigger" aria-label="Change language">
							<span class="lang-flag">{currentLang.flag}</span>
						</span>
					{/snippet}
					{#snippet content()}
						<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
						<div
							class="dropdown-menu"
							onclick={(e) => closeOnLinkClick(e, () => (langDropdown = false))}
						>
							{#each LANGUAGES_CONFIG as lang (lang.code)}
								<a
									class="dropdown-link lang-link"
									class:active={lang.code === currentLang.code}
									href={buildMarketingUrl(
										$page.url.pathname,
										currentLang.code,
										lang.code
									) || '/'}
								>
									<span class="lang-flag">{lang.flag}</span>
									{lang.name}
									<span class="lang-item-code">{lang.code.toUpperCase()}</span>
								</a>
							{/each}
						</div>
					{/snippet}
				</Dropdown>
			</div>
		</nav>

		<div class="end">
			<Button href="/console" as="a">Go to Console &rarr;</Button>
		</div>

		<span class="mobile-nav-wrap">
			<Dropdown bind:show={mobileOpen} align="end" width={260} contentPadding={8}>
				{#snippet trigger()}
					<span class="icon-btn" aria-label="Menu" role="button" tabindex="0">
						{#if mobileOpen}
							<IconX size={18} />
						{:else}
							<IconList size={18} />
						{/if}
					</span>
				{/snippet}
				{#snippet content()}
					<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
					<div
						class="dropdown-menu"
						onclick={(e) => closeOnLinkClick(e, () => (mobileOpen = false))}
					>
						<a class="dropdown-link" href="/pricing">Pricing</a>
						<a class="dropdown-link" href="/docs">Docs</a>
						<a class="dropdown-link" href="/hosting">Hosting</a>
						<a class="dropdown-link" href="/themes">Themes</a>
						<a class="dropdown-link" href="/integrations">Integrations</a>
						<a
							class="dropdown-link"
							href="https://github.com/hyvor/blogs"
							target="_blank"
						>
							Github
						</a>
						<div class="mobile-divider"></div>
						<Button href="/console" as="a">Go to Console &rarr;</Button>
					</div>
				{/snippet}
			</Dropdown>
		</span>
	</div>
</header>

<div class="header-space"></div>

<style>
	:global(html) {
		scroll-padding-top: calc(var(--header-height) + 20px);
	}

	.header-space {
		height: var(--header-height);
	}

	.site-header {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		z-index: 100;
		height: var(--header-height);
		display: flex;
		align-items: center;
		background: var(--background, var(--accent-lightest));
		border-bottom: 1px solid transparent;
		transition:
			border-color 0.2s ease,
			box-shadow 0.2s ease;
	}

	.site-header.scrolled {
		border-bottom-color: var(--border);
	}

	.header-inner {
		display: flex;
		align-items: center;
		gap: 16px;
	}

	.brand {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		color: var(--text);
		text-decoration: none;
		font-size: 15px;
		font-weight: 700;
		white-space: nowrap;
	}

	.brand img {
		display: block;
	}

	.lang-toggle {
		position: relative;
		margin-left: 12px;
		padding-left: 12px;
	}

	/* a shorter divider than a full-height border-left — centered on the toggle */
	.lang-toggle::before {
		content: '';
		position: absolute;
		left: 0;
		top: 50%;
		transform: translateY(-50%);
		width: 1px;
		height: 16px;
		background: var(--border);
	}

	.lang-flag {
		font-size: 18px;
		line-height: 1;
	}

	.lang-link {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.lang-item-code {
		margin-left: auto;
		padding-left: 8px;
		font-size: 11px;
		font-weight: 600;
		color: var(--text-light);
	}

	.center {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 2px;
	}

	.nav-link {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		padding: 6px 16px;
		border-radius: 20px;
		font-size: 13px;
		font-weight: 500;
		color: var(--text-light);
		text-decoration: none;
		cursor: pointer;
		transition:
			0.15s background-color,
			0.15s color;
	}

	.nav-link:hover {
		background: var(--hover, var(--accent-lightest));
		color: var(--text);
	}

	.nav-link.active {
		background: var(--accent-light);
		color: var(--text);
	}

	.nav-trigger {
		user-select: none;
	}

	.end {
		display: flex;
		align-items: center;
	}

	.icon-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 32px;
		height: 32px;
		border-radius: 8px;
		color: var(--text);
		cursor: pointer;
	}

	.icon-btn:hover {
		background: var(--hover, var(--accent-lightest));
	}

	.mobile-nav-wrap {
		display: none;
	}

	.dropdown-menu {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.dropdown-link {
		padding: 8px 10px;
		/* the outer box radius is 20px (--box-radius) and its padding is 8px
		   (Dropdown's contentPadding below) — 20 - 8 = 12 keeps the gap between
		   the box edge and this pill visually even all the way around,
		   including through the corners, instead of pinching at the diagonal */
		border-radius: 12px;
		font-size: 13px;
		font-weight: 500;
		color: var(--text-light);
		text-decoration: none;
		transition:
			0.15s background-color,
			0.15s color;
	}

	.dropdown-link:hover {
		background: var(--hover, var(--accent-lightest));
		color: var(--text);
	}

	.dropdown-link.active {
		background: var(--accent-light);
		color: var(--text);
	}

	.mobile-divider {
		height: 1px;
		background: var(--border);
		margin: 6px 4px;
	}

	.mobile-cta {
		justify-content: center;
		margin: 2px 2px 0;
	}

	@media screen and (max-width: 992px) {
		.center,
		.end {
			display: none;
		}

		.mobile-nav-wrap {
			display: inline-flex;
			margin-left: auto;
		}
	}
</style>
