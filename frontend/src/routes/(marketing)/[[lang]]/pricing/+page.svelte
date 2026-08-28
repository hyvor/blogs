<script lang="ts">
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import FeatureSectionTitle from '../../@homepage/FeatureSectionTitle.svelte';
	import Faqs from '../../@components/Faqs.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import Features from './Features.svelte';
	import SelfHost from './SelfHost.svelte';
	import { getMarketingI18n, getStaticString } from '../marketingLang';

	const I18n = getMarketingI18n();
	// I18n.t() runs every string through ICU MessageFormat, which chokes on
	// HTML with attributes (e.g. <a href="...">) — INVALID_TAG. The FAQ
	// answers embed real links/markup, so they're read as raw strings from
	// I18n's own (already locale + fallback merged) strings store instead,
	// bypassing ICU parsing entirely, same as the original hardcoded HTML.
	const strings = I18n.strings;

	const faqs = $derived(
		[1, 2, 3, 4, 5, 6, 7].map((n) => ({
			q: I18n.t(`pricing.faq.q${n}` as never),
			a: getStaticString($strings, `pricing.faq.a${n}`)
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
