<script lang="ts">
	import FullTrialSignup from './../../@components/FullTrialSignup.svelte';
	import HomepageCustomers from './../../@homepage/Customers/HomepageCustomers.svelte';
	import AllFeatures from './../../@homepage/AllFeatures/AllFeatures.svelte';
	import MultiLanguage from './../../@homepage/MultiLanguage/MultiLanguage.svelte';
	import CustomThemes from './../../@homepage/CustomThemes/CustomThemes.svelte';
	import Reviews from '../../@components/Reviews.svelte';
	import AboveFold from '../../@homepage/AboveFold.svelte';
	import Hosting from '../../@homepage/Hosting/Hosting.svelte';
	import Seo from '../../@homepage/Seo/Seo.svelte';

	let { data } = $props();
</script>

<svelte:head>
	<title>{data.title} - Hyvor Blogs</title>
	<meta name="description" content={data.subtitle} />
	<link rel="canonical" href={`https://hyvor.com/blogs/${data.slug}`} />
</svelte:head>

<AboveFold integrationIcon={data.icon} title={data.title} subtitle={data.subtitle} />
<Reviews />
<CustomThemes />
<Hosting />
<Seo />
<MultiLanguage />
<AllFeatures />
<HomepageCustomers />
<FullTrialSignup title={data.ctaTitle} />
