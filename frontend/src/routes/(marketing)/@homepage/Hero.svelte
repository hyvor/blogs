<script lang="ts">
	import { Hero } from '@hyvor/design/marketing';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();
</script>

<Hero fullHeight buttons={{ href: '/console?signup', label: 'Start your blog', size: 'x-large' }}>
	{#snippet title()}
		{I18n.t('homepage.hero.title')}<br /><span class="heading-small"
			>({I18n.t('homepage.hero.title2')})</span
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
