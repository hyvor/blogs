<script lang="ts">
	import { Hero, FeatureSplit, FAQ } from '@hyvor/design/marketing';
	import IconGlobeEuropeAfrica from '@hyvor/icons/IconGlobeEuropeAfrica';
	import IconPinMapFill from '@hyvor/icons/IconPinMapFill';
	import IconServer from '@hyvor/icons/IconServer';
	import IconShieldCheck from '@hyvor/icons/IconShieldCheck';
	import IconLockFill from '@hyvor/icons/IconLockFill';
	import IconFileEarmarkLock from '@hyvor/icons/IconFileEarmarkLock';
	import IconPersonFillCheck from '@hyvor/icons/IconPersonFillCheck';
	import IconShieldLockFill from '@hyvor/icons/IconShieldLockFill';
	import OwnershipMockup from '../../@homepage/OwnershipMockup.svelte';
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import TerminalMockup from '../../@components/TerminalMockup.svelte';
	import { getMarketingI18n, getStaticString } from '../marketingLang';

	const I18n = getMarketingI18n();
	const strings = I18n.strings;

	const faqs = $derived(
		['Company', 'Hosting', 'Gdpr', 'Trackers', 'SelfHost', 'Export'].map((topic) => ({
			q: I18n.t(`eu.faq.question${topic}` as never),
			a: getStaticString($strings, `eu.faq.answer${topic}`)
		}))
	);
</script>

<svelte:head>
	<title>{I18n.t('eu.seo.title')}</title>
	<meta name="description" content={I18n.t('eu.seo.description')} />
</svelte:head>

<div class="hero-wrap">
	<Hero
		badge={I18n.t('eu.hero.badge')}
		title={I18n.t('eu.hero.title')}
		buttons={[
			{ href: '/console?signup', label: I18n.t('eu.hero.buttonStart'), size: 'x-large' },
			{
				href: '/',
				label: I18n.t('eu.hero.buttonLearn'),
				size: 'x-large',
				variant: 'outline'
			}
		]}
		subtitle={I18n.t('eu.hero.subtitle')}
	></Hero>
</div>

<FeatureSplit
	eyebrow={I18n.t('eu.residency.eyebrow')}
	title={I18n.t('eu.residency.title')}
	description={I18n.t('eu.residency.description')}
	bullets={[
		I18n.t('eu.residency.bulletHetzner'),
		I18n.t('eu.residency.bulletCompany'),
		I18n.t('eu.residency.bulletNoTransfer')
	]}
>
	{#snippet visual()}
		<div class="eu-mockup">
			<div class="eu-globe">
				<IconGlobeEuropeAfrica size={64} />
				<div class="eu-globe-badge"><IconPinMapFill size={20} /></div>
			</div>
			<div class="eu-location-card">
				<IconServer size={18} />
				<div>
					<div class="eu-location-title">{I18n.t('eu.residency.locationTitle')}</div>
					<div class="eu-location-sub">{I18n.t('eu.residency.locationSubtitle')}</div>
				</div>
			</div>
			<div class="eu-badges-row">
				<span class="eu-icon-badge"><IconShieldCheck size={22} /></span>
				<span class="eu-icon-badge"><IconLockFill size={22} /></span>
			</div>
		</div>
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('eu.gdpr.eyebrow')}
	title={I18n.t('eu.gdpr.title')}
	description={I18n.t('eu.gdpr.description')}
	bullets={[
		I18n.t('eu.gdpr.bulletDpa'),
		I18n.t('eu.gdpr.bulletRights'),
		I18n.t('eu.gdpr.bulletSubprocessors')
	]}
	button={[
		{
			href: 'https://hyvor.com/compliance',
			label: I18n.t('eu.gdpr.buttonCompliance'),
			external: true
		}
	]}
	flip
	altBg
>
	{#snippet visual()}
		<div class="eu-mockup">
			<div class="eu-globe">
				<IconShieldLockFill size={64} />
			</div>
			<div class="eu-badges-row">
				<span class="eu-icon-badge"><IconFileEarmarkLock size={22} /></span>
				<span class="eu-icon-badge"><IconPersonFillCheck size={22} /></span>
				<span class="eu-icon-badge"><IconShieldCheck size={22} /></span>
			</div>
		</div>
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('eu.ownership.eyebrow')}
	title={I18n.t('eu.ownership.title')}
	description={I18n.t('eu.ownership.description')}
	bullets={[
		I18n.t('eu.ownership.bulletExport'),
		I18n.t('eu.ownership.bulletSelfHost'),
		I18n.t('eu.ownership.bulletNoTrackers')
	]}
>
	{#snippet visual()}
		<OwnershipMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('eu.selfHost.eyebrow')}
	title={I18n.t('eu.selfHost.title')}
	description={I18n.t('eu.selfHost.description')}
	bullets={[
		I18n.t('eu.selfHost.bulletFree'),
		I18n.t('eu.selfHost.bulletDocker'),
		I18n.t('eu.selfHost.bulletOwnership')
	]}
	button={[
		{ href: '/hosting', label: I18n.t('eu.selfHost.buttonLearn'), external: true },
		{
			href: 'https://github.com/hyvor/blogs',
			label: I18n.t('eu.selfHost.buttonGithub'),
			external: true
		}
	]}
	flip
	altBg
>
	{#snippet visual()}
		<TerminalMockup output="Hyvor Blogs running at https://acme.eu/blog" />
	{/snippet}
</FeatureSplit>

<div class="eu-faq-wrap hds-container">
	<FAQ items={faqs} columns={1} />
</div>

<FullTrialSignup title={I18n.t('eu.trialSignup.title')} style="margin-top:60px" />

<style>
	.hero-wrap :global(.scroll-indicator) {
		display: none;
	}

	.eu-mockup {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 26px;
		padding: 44px 20px;
	}

	.eu-globe {
		position: relative;
		width: 104px;
		height: 104px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.eu-globe-badge {
		position: absolute;
		right: -8px;
		bottom: -8px;
		width: 48px;
		height: 48px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: var(--accent);
		color: var(--background);
		transform: rotate(-10deg);
	}

	.eu-location-card {
		display: flex;
		align-items: center;
		gap: 12px;
		padding: 14px 18px;
		border-radius: 14px;
		background: var(--box-background);
		box-shadow: var(--box-shadow);
		color: var(--accent);
	}

	.eu-location-title {
		font-size: 14px;
		font-weight: 600;
		color: var(--text);
	}

	.eu-location-sub {
		font-size: 13px;
		color: var(--text-light);
	}

	.eu-badges-row {
		display: flex;
		gap: 12px;
	}

	.eu-icon-badge {
		width: 48px;
		height: 48px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.eu-faq-wrap {
		padding-top: 20px;
		padding-bottom: 20px;
		margin-top: 40px;
		margin-bottom: 40px;
		max-width: min(1100px, 100%);
		margin-left: auto;
		margin-right: auto;
	}
</style>
