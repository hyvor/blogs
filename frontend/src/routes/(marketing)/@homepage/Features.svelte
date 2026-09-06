<script lang="ts">
	import { FeatureSplit } from '@hyvor/design/marketing';
	import ThemesLivePreview from './ThemesLivePreview.svelte';
	import SeoMockup from './SeoMockup.svelte';
	import HostingMockup from './HostingMockup.svelte';
	import MultiLanguageMockup from './MultiLanguageMockup.svelte';
	import SuiteMockup from './SuiteMockup.svelte';
	import OwnershipMockup from './OwnershipMockup.svelte';
	import AllInOneMockup from './AllInOneMockup.svelte';
	import AiScreencast from './AiScreencast.svelte';
	import SuggestionScreencast from './SuggestionScreencast.svelte';
	import LinkAnalyzerMockup from './LinkAnalyzerMockup.svelte';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();
</script>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.ownership.eyebrow')}
	title={I18n.t('homepage.features.ownership.title')}
	description={I18n.t('homepage.features.ownership.description')}
	bullets={[
		I18n.t('homepage.features.ownership.bulletExport'),
		I18n.t('homepage.features.ownership.bulletLicense'),
		I18n.t('homepage.features.ownership.bulletNoTrackers')
	]}
	altBg
>
	{#snippet visual()}
		<OwnershipMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.allInOne.eyebrow')}
	title={I18n.t('homepage.features.allInOne.title')}
	description={I18n.t('homepage.features.allInOne.description')}
	bullets={[
		I18n.t('homepage.features.allInOne.bulletNoPlugins'),
		I18n.t('homepage.features.allInOne.bulletUpgrades'),
		I18n.t('homepage.features.allInOne.bulletNoExtraCharges')
	]}
	flip
>
	{#snippet visual()}
		<AllInOneMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.themes.eyebrow')}
	title={I18n.t('homepage.features.themes.title')}
	description={I18n.t('homepage.features.themes.description')}
	bullets={[
		I18n.t('homepage.features.themes.bulletOpenSource'),
		I18n.t('homepage.features.themes.bulletDocs'),
		I18n.t('homepage.features.themes.bulletHeadless')
	]}
	button={[
		{ href: '/themes', label: I18n.t('homepage.features.themes.buttonBrowse'), external: true },
		{ href: '/docs/headless', label: I18n.t('homepage.features.themes.buttonHeadlessDocs') }
	]}
	altBg
	overlap
>
	{#snippet visual()}
		<ThemesLivePreview />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.seo.eyebrow')}
	title={I18n.t('homepage.features.seo.title')}
	description={I18n.t('homepage.features.seo.description')}
	bullets={[
		I18n.t('homepage.features.seo.bulletMetaTags'),
		I18n.t('homepage.features.seo.bulletSitemaps'),
		I18n.t('homepage.features.seo.bulletAnalyzers'),
		I18n.t('homepage.features.seo.bulletStaticHtml'),
		I18n.t('homepage.features.seo.bulletWebp')
	]}
	flip
>
	{#snippet visual()}
		<SeoMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.hosting.eyebrow')}
	title={I18n.t('homepage.features.hosting.title')}
	description={I18n.t('homepage.features.hosting.description')}
	bullets={[
		I18n.t('homepage.features.hosting.bulletTls'),
		I18n.t('homepage.features.hosting.bulletPlatforms'),
		I18n.t('homepage.features.hosting.bulletReverseProxy')
	]}
	altBg
>
	{#snippet visual()}
		<HostingMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.suite.eyebrow')}
	title={I18n.t('homepage.features.suite.title')}
	description={I18n.t('homepage.features.suite.description')}
	bullets={[
		I18n.t('homepage.features.suite.bulletTalk'),
		I18n.t('homepage.features.suite.bulletPost'),
		I18n.t('homepage.features.suite.bulletOneAccount')
	]}
	button={{ href: '/pricing', label: I18n.t('homepage.features.suite.button') }}
	flip
>
	{#snippet visual()}
		<SuiteMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.ai.eyebrow')}
	title={I18n.t('homepage.features.ai.title')}
	description={I18n.t('homepage.features.ai.description')}
	bullets={[
		I18n.t('homepage.features.ai.bulletGeneration'),
		I18n.t('homepage.features.ai.bulletSuggestions'),
		I18n.t('homepage.features.ai.bulletBulkEditing')
	]}
	altBg
	overlap
>
	{#snippet visual()}
		<AiScreencast />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.collaboration.eyebrow')}
	title={I18n.t('homepage.features.collaboration.title')}
	description={I18n.t('homepage.features.collaboration.description')}
	bullets={[
		I18n.t('homepage.features.collaboration.bulletLiveCursors'),
		I18n.t('homepage.features.collaboration.bulletSuggestionMode')
	]}
	flip
	overlap
>
	{#snippet visual()}
		<SuggestionScreencast />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.multiLanguage.eyebrow')}
	title={I18n.t('homepage.features.multiLanguage.title')}
	description={I18n.t('homepage.features.multiLanguage.description')}
	bullets={[
		I18n.t('homepage.features.multiLanguage.bulletRtl'),
		I18n.t('homepage.features.multiLanguage.bulletTranslator'),
		I18n.t('homepage.features.multiLanguage.bulletHreflang')
	]}
	altBg
>
	{#snippet visual()}
		<MultiLanguageMockup />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.linkAnalyzer.eyebrow')}
	title={I18n.t('homepage.features.linkAnalyzer.title')}
	description={I18n.t('homepage.features.linkAnalyzer.description')}
	bullets={[
		I18n.t('homepage.features.linkAnalyzer.bulletPeriodicChecks'),
		I18n.t('homepage.features.linkAnalyzer.bulletReports'),
		I18n.t('homepage.features.linkAnalyzer.bulletBrokenLinks')
	]}
	flip
>
	{#snippet visual()}
		<LinkAnalyzerMockup />
	{/snippet}
</FeatureSplit>
