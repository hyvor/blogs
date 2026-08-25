<script lang="ts">
	import { FeatureSplit } from '@hyvor/design/marketing';
	import ThemesLivePreview from './ThemesLivePreview.svelte';
	import SeoMockup from './SeoMockup.svelte';
	import HostingMockup from './HostingMockup.svelte';
	import MultiLanguageMockup from './MultiLanguageMockup.svelte';
	import SuiteMockup from './SuiteMockup.svelte';
	import OwnershipMockup from './OwnershipMockup.svelte';
	import AllInOneMockup from './AllInOneMockup.svelte';
	import AiMockup from './AiMockup.svelte';
	import LinkAnalyzerMockup from './LinkAnalyzerMockup.svelte';
	import { getMarketingI18n } from '../[[lang]]/marketingLang';

	const I18n = getMarketingI18n();
</script>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.ownership.eyebrow')}
	title={I18n.t('homepage.features.ownership.title')}
	description={I18n.t('homepage.features.ownership.description')}
	bullets={[
		I18n.t('homepage.features.ownership.bullet1'),
		I18n.t('homepage.features.ownership.bullet2'),
		I18n.t('homepage.features.ownership.bullet3')
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
		I18n.t('homepage.features.allInOne.bullet1'),
		I18n.t('homepage.features.allInOne.bullet2'),
		I18n.t('homepage.features.allInOne.bullet3')
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
		I18n.t('homepage.features.themes.bullet1'),
		I18n.t('homepage.features.themes.bullet2'),
		I18n.t('homepage.features.themes.bullet3')
	]}
	button={[
		{ href: '/themes', label: I18n.t('homepage.features.themes.button1'), external: true },
		{ href: '/docs/headless', label: I18n.t('homepage.features.themes.button2') }
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
		I18n.t('homepage.features.seo.bullet1'),
		I18n.t('homepage.features.seo.bullet2'),
		I18n.t('homepage.features.seo.bullet3'),
		I18n.t('homepage.features.seo.bullet4'),
		I18n.t('homepage.features.seo.bullet5')
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
		I18n.t('homepage.features.hosting.bullet1'),
		I18n.t('homepage.features.hosting.bullet2'),
		I18n.t('homepage.features.hosting.bullet3')
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
		I18n.t('homepage.features.suite.bullet1'),
		I18n.t('homepage.features.suite.bullet2'),
		I18n.t('homepage.features.suite.bullet3')
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
		I18n.t('homepage.features.ai.bullet1'),
		I18n.t('homepage.features.ai.bullet2'),
		I18n.t('homepage.features.ai.bullet3')
	]}
	interactiveBullets
	altBg
>
	{#snippet visual(active)}
		<AiMockup {active} />
	{/snippet}
</FeatureSplit>

<FeatureSplit
	eyebrow={I18n.t('homepage.features.multiLanguage.eyebrow')}
	title={I18n.t('homepage.features.multiLanguage.title')}
	description={I18n.t('homepage.features.multiLanguage.description')}
	bullets={[
		I18n.t('homepage.features.multiLanguage.bullet1'),
		I18n.t('homepage.features.multiLanguage.bullet2'),
		I18n.t('homepage.features.multiLanguage.bullet3')
	]}
	flip
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
		I18n.t('homepage.features.linkAnalyzer.bullet1'),
		I18n.t('homepage.features.linkAnalyzer.bullet2'),
		I18n.t('homepage.features.linkAnalyzer.bullet3')
	]}
>
	{#snippet visual()}
		<LinkAnalyzerMockup />
	{/snippet}
</FeatureSplit>
