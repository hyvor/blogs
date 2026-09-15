<script lang="ts">
	import { Hero, FeatureSplit, FAQ } from '@hyvor/design/marketing';
	import IconTranslate from '@hyvor/icons/IconTranslate';
	import IconTag from '@hyvor/icons/IconTag';
	import IconPersonFillCheck from '@hyvor/icons/IconPersonFillCheck';
	import MultiLanguageMockup from '../../@homepage/MultiLanguageMockup.svelte';
	import SeoMockup from '../../@homepage/SeoMockup.svelte';
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import { getMarketingI18n, getStaticString } from '../marketingLang';

	const I18n = getMarketingI18n();
	const strings = I18n.strings;

	const faqs = $derived(
		['Plugin', 'Rtl', 'Url', 'Seo', 'Theme', 'HowMany'].map((topic) => ({
			q: I18n.t(`multiLanguage.faq.question${topic}` as never),
			a: getStaticString($strings, `multiLanguage.faq.answer${topic}`)
		}))
	);
</script>

<svelte:head>
	<title>{I18n.t('multiLanguage.seo.title')}</title>
	<meta name="description" content={I18n.t('multiLanguage.seo.description')} />
</svelte:head>

<div class="hero-wrap">
	<Hero
		badge={I18n.t('multiLanguage.hero.badge')}
		title={I18n.t('multiLanguage.hero.title')}
		subtitle={I18n.t('multiLanguage.hero.subtitle')}
		buttons={[
			{ href: '/console?signup', label: I18n.t('multiLanguage.hero.buttonStart'), size: 'x-large' },
			{
				href: '/docs/languages',
				label: I18n.t('multiLanguage.hero.buttonDocs'),
				size: 'x-large',
				variant: 'outline'
			}
		]}
	/>
</div>

<FeatureSplit
	eyebrow={I18n.t('multiLanguage.builtIn.eyebrow')}
	title={I18n.t('multiLanguage.builtIn.title')}
	description={I18n.t('multiLanguage.builtIn.description')}
	bullets={[
		I18n.t('multiLanguage.builtIn.bulletAddLanguage'),
		I18n.t('multiLanguage.builtIn.bulletCodeNameDirection'),
		I18n.t('multiLanguage.builtIn.bulletRouting')
	]}
>
	{#snippet visual()}
		<MultiLanguageMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('multiLanguage.translate.eyebrow')}
	title={I18n.t('multiLanguage.translate.title')}
	description={I18n.t('multiLanguage.translate.description')}
	bullets={[
		I18n.t('multiLanguage.translate.bulletPosts'),
		I18n.t('multiLanguage.translate.bulletData'),
		I18n.t('multiLanguage.translate.bulletSeo')
	]}
	flip
	altBg
>
	{#snippet visual()}
		<div class="ml-mockup">
			<div class="ml-icon">
				<IconTranslate size={64} />
			</div>
			<div class="ml-badges-row">
				<span class="ml-icon-badge"><IconTag size={22} /></span>
				<span class="ml-icon-badge"><IconPersonFillCheck size={22} /></span>
			</div>
		</div>
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('multiLanguage.technicalSeo.eyebrow')}
	title={I18n.t('multiLanguage.technicalSeo.title')}
	description={I18n.t('multiLanguage.technicalSeo.description')}
	bullets={[
		I18n.t('multiLanguage.technicalSeo.bulletHreflang'),
		I18n.t('multiLanguage.technicalSeo.bulletLangAttribute'),
		I18n.t('multiLanguage.technicalSeo.bulletRtl')
	]}
>
	{#snippet visual()}
		<SeoMockup />
	{/snippet}
</FeatureSplit>

<div class="faq-wrap hds-container">
	<FAQ items={faqs} columns={1} />
</div>

<FullTrialSignup title={I18n.t('multiLanguage.trialSignup.title')} style="margin-top:60px" />

<style>
	.hero-wrap :global(.scroll-indicator) {
		display: none;
	}

	.ml-mockup {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 26px;
		padding: 44px 20px;
	}

	.ml-icon {
		width: 104px;
		height: 104px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.ml-badges-row {
		display: flex;
		gap: 12px;
	}

	.ml-icon-badge {
		width: 48px;
		height: 48px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.faq-wrap {
		padding-top: 20px;
		padding-bottom: 20px;
		margin-top: 40px;
		margin-bottom: 40px;
		max-width: min(1100px, 100%);
		margin-left: auto;
		margin-right: auto;
	}
</style>
