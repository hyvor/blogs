<script>
	import Hero from './@homepage/Hero.svelte';
	import LogoStrip from './@homepage/LogoStrip.svelte';
	import StatsRow from './@homepage/StatsRow.svelte';
	import Features from './@homepage/Features.svelte';
	import Testimonials from './@homepage/Testimonials.svelte';
	import AllFeatures from './@homepage/AllFeatures/AllFeatures.svelte';
	import FullTrialSignup from './@components/FullTrialSignup.svelte';
</script>

<svelte:head>
	<title>Hyvor Blogs - Open-source Blogging Platform</title>
	<meta
		name="description"
		content="Hyvor Blogs is an open-source blogging platform that's fast and simple. Create a blog, manage it, and grow it without managing servers or databases. Self-host or let us handle it."
	/>
	<link rel="canonical" href="https://blogs.hyvor.com" />
</svelte:head>

<Hero />
<!-- <StatsRow /> -->
<!-- <LogoStrip /> -->
<Features />
<Testimonials />
<AllFeatures />
<FullTrialSignup />
