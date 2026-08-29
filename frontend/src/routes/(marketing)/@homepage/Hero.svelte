<script lang="ts">
	import { Hero } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();
</script>

<Hero
	fullHeight
	buttons={{ href: '/console?signup', label: I18n.t('homepage.hero.button'), size: 'x-large' }}
>
	{#snippet title()}
		{I18n.t('homepage.hero.title')}<br /><span class="heading-small"
			>{I18n.t('homepage.hero.title2')}</span
		>
	{/snippet}

	{#snippet subtitle()}
		<I18n.T
			key="homepage.hero.subtitle"
			params={{
				marker: {
					element: 'span',
					props: { class: 'hds-marker' }
				}
			}}
		/>
		<br />
		<I18n.T
			key="homepage.hero.subtitle2"
			params={{
				hl: {
					element: 'span',
					props: { class: 'hds-hl' }
				}
			}}
		/>
	{/snippet}
</Hero>

<style>
	/* the marked phrase (e.g. "blogging platform" / "plateforme de blogs")
	   reads oddly split across two lines when a translation runs longer
	   than English - keep it together even if that means it's the widest
	   line in the subtitle */
	:global(.hds-marker) {
		white-space: nowrap;
	}

	/* the design system's .subtitle max-width (480px) is narrow enough that
	   longer translations (e.g. French) wrap the first sentence onto two
	   lines by itself, pushing the whole subtitle to three lines total -
	   widen it so "Hyvor Blogs est notre plateforme de blogs" fits on one
	   line, back to two lines overall. !important because that class is
	   compiled with a Svelte scoping class we can't otherwise out-specify;
	   scoped to `.hero .subtitle` (not a bare `:global(.subtitle)`) since
	   other pages have their own unrelated `.subtitle` elements */
	:global(.hero .subtitle) {
		max-width: 640px !important;
	}
</style>
