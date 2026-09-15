<script lang="ts">
	import { Hero, FeatureSplit, FAQ } from '@hyvor/design/marketing';
	import OwnershipMockup from '../../@homepage/OwnershipMockup.svelte';
	import HostingMockup from '../../@homepage/HostingMockup.svelte';
	import TerminalMockup from '../../@components/TerminalMockup.svelte';
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import { getMarketingI18n, getStaticString } from '../marketingLang';

	const I18n = getMarketingI18n();
	const strings = I18n.strings;
</script>

<svelte:head>
	<title>{I18n.t('selfHosted.seo.title')}</title>
	<meta name="description" content={I18n.t('selfHosted.seo.description')} />
</svelte:head>

<div class="hero-wrap">
	<Hero
		badge={I18n.t('selfHosted.hero.badge')}
		title={I18n.t('selfHosted.hero.title')}
		subtitle={I18n.t('selfHosted.hero.subtitle')}
		buttons={[
			{ href: '/hosting', label: I18n.t('selfHosted.hero.buttonDeploy'), size: 'x-large' },
			{
				href: 'https://github.com/hyvor/blogs',
				label: I18n.t('selfHosted.hero.buttonGithub'),
				size: 'x-large',
				variant: 'outline',
				external: true
			}
		]}
	/>
</div>

<FeatureSplit
	eyebrow={I18n.t('selfHosted.deploy.eyebrow')}
	title={I18n.t('selfHosted.deploy.title')}
	description={I18n.t('selfHosted.deploy.description')}
	bullets={[I18n.t('selfHosted.deploy.bulletCompose'), I18n.t('selfHosted.deploy.bulletServer')]}
	button={[{ href: '/hosting/deploy', label: I18n.t('selfHosted.deploy.buttonDocs') }]}
>
	{#snippet visual()}
		<TerminalMockup output="Hyvor Blogs running at https://blog.example.com" />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('selfHosted.ownership.eyebrow')}
	title={I18n.t('selfHosted.ownership.title')}
	description={I18n.t('selfHosted.ownership.description')}
	bullets={[
		I18n.t('selfHosted.ownership.bulletFree'),
		I18n.t('selfHosted.ownership.bulletOwnership'),
		I18n.t('selfHosted.ownership.bulletNoLockIn')
	]}
	flip
	altBg
>
	{#snippet visual()}
		<OwnershipMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('selfHosted.production.eyebrow')}
	title={I18n.t('selfHosted.production.title')}
	description={I18n.t('selfHosted.production.description')}
	bullets={[
		I18n.t('selfHosted.production.bulletTls'),
		I18n.t('selfHosted.production.bulletMultiDomain'),
		I18n.t('selfHosted.production.bulletReverseProxy')
	]}
>
	{#snippet visual()}
		<HostingMockup />
	{/snippet}
</FeatureSplit>

<FullTrialSignup title={I18n.t('selfHosted.trialSignup.title')} style="margin-top:60px" />

<style>
	.hero-wrap :global(.scroll-indicator) {
		display: none;
	}
</style>
