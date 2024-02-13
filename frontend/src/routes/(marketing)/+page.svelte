<script>
	import CustomThemes from './@homepage/CustomThemes/CustomThemes.svelte';
	import Reviews from './@components/Reviews.svelte';
	import AboveFold from './@homepage/AboveFold.svelte';
	import Hosting from './@homepage/Hosting/Hosting.svelte';
	import Seo from './@homepage/Seo/Seo.svelte';
	import MultiLanguage from './@homepage/MultiLanguage/MultiLanguage.svelte';
	import FullTrialSignup from './@components/FullTrialSignup.svelte';
	import AllFeatures from './@homepage/AllFeatures/AllFeatures.svelte';
	import HomepageCustomers from './@homepage/Customers/HomepageCustomers.svelte';
</script>

<svelte:head>
    <title>Hyvor Blogs - All-in-one Blogging Platform</title>
    <meta name="description" content="Hyvor Blogs is a powerful blogging platform that allows you to create a blog, customize it, and start publishing articles instantly. In-built SEO, multi-language support, custom domains with SSL, and many more features included!">
	<link rel="canonical" href="https://blogs.hyvor.com">
</svelte:head>

<AboveFold 
	title="All-in-one Blogging Platform"
	subtitle="Hyvor Blogs is a platform to create a blog, manage it, and grow it without having to worry about managing servers, databases, and other technical stuff."
/>
<Reviews />
<CustomThemes />
<Hosting />
<Seo />
<MultiLanguage />
<AllFeatures />
<HomepageCustomers />
<FullTrialSignup />