<script>
	import { MARKETING_PAGE_META } from '../marketing';
	import Hero from '../@homepage/Hero.svelte';
	import LogoStrip from '../@homepage/LogoStrip.svelte';
	import Features from '../@homepage/Features.svelte';
	import Testimonials from '../@homepage/Testimonials.svelte';
	import Enterprise from '../@homepage/Enterprise.svelte';
	import AllFeatures from '../@homepage/AllFeatures/AllFeatures.svelte';
	import FullTrialSignup from '../@components/FullTrialSignup.svelte';
	import { getMarketingI18n } from './marketingLang';

	const I18n = getMarketingI18n();
</script>

<svelte:head>
	<title>{I18n.t('seo.title')}</title>
	<meta name="description" content={I18n.t('seo.description')} />
</svelte:head>

<Hero />
<LogoStrip />
<Features />
<Enterprise />
<AllFeatures />
<Testimonials />
<FullTrialSignup />
