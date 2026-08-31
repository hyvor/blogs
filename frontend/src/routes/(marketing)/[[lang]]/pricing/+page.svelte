<script lang="ts">
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import FeatureSectionTitle from '../../@homepage/FeatureSectionTitle.svelte';
	import Faqs from '../../@components/Faqs.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import Features from './Features.svelte';
	import SelfHost from './SelfHost.svelte';
	import { getMarketingI18n, getStaticString } from '../marketingLang';

	const I18n = getMarketingI18n();
	const strings = I18n.strings;

	const faqTopics = ['Trial', 'Themes', 'Discounts', 'Ownership', 'Limits', 'Ads', 'Comments'];

	const faqs = $derived(
		faqTopics.map((topic) => ({
			q: I18n.t(`pricing.faq.question${topic}` as never),
			a: getStaticString($strings, `pricing.faq.answer${topic}`)
		}))
	);
</script>

<svelte:head>
	<title>{I18n.t('pricing.pageTitle')}</title>
</svelte:head>

<PricingPlans />
<SelfHost />
<Features />

<FeatureSectionTitle
	title={I18n.t('pricing.faq.title')}
	h2Style="font-family: var(--font-serif); font-weight: 700; letter-spacing: -0.01em;"
/>

<div class="faqs-wrap hds-container">
	<Faqs items={faqs} />
</div>

<FullTrialSignup style="margin-top:130px" />

<style>
	.faqs-wrap {
		padding-top: 20px;
		padding-bottom: 20px;
		margin-top: 20px;
		margin-bottom: 40px;
		max-width: min(1100px, 100%);
		margin-left: auto;
		margin-right: auto;
	}
</style>
