<script>
	import { MARKETING_PAGE_META } from './marketing';
	import Hero from './@homepage/Hero.svelte';
	import LogoStrip from './@homepage/LogoStrip.svelte';
	import StatsRow from './@homepage/StatsRow.svelte';
	import Features from './@homepage/Features.svelte';
	import Testimonials from './@homepage/Testimonials.svelte';
	import Enterprise from './@homepage/Enterprise.svelte';
	import AllFeatures from './@homepage/AllFeatures/AllFeatures.svelte';
	import FullTrialSignup from './@components/FullTrialSignup.svelte';
</script>

<svelte:head>
	<title>{MARKETING_PAGE_META.title}</title>
	<meta name="description" content={MARKETING_PAGE_META.description} />
</svelte:head>

<Hero />
<!-- <StatsRow /> -->
<!-- <LogoStrip /> -->
<Features />
<Enterprise />
<AllFeatures />
<Testimonials />
<FullTrialSignup />
