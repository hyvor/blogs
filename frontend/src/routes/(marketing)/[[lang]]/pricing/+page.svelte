<script lang="ts">
	import FullTrialSignup from '../../@components/FullTrialSignup.svelte';
	import FeatureSectionTitle from '../../@homepage/FeatureSectionTitle.svelte';
	import Faqs from '../../@components/Faqs.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import Features from './Features.svelte';
	import SelfHost from './SelfHost.svelte';

	const faqs = [
		{
			q: 'How does the free trial work?',
			a: 'Anyone can signup for the <strong>14-day free trial</strong> to test Hyvor Blogs. No credit card required. All features are available during the trial (except Hyvor Talk integration). After the trial, you can upgrade to a paid plan to continue using Hyvor Blogs.'
		},
		{
			q: 'Do I have to pay for themes?',
			a: 'No, all official themes are <strong>free and <a href="https://github.com/hyvor/hyvor-blogs-themes" target="_blank" rel="noopener">open-source</a></strong>. They can be easily installed on your blog with a few clicks. If you want to build your own theme, you will have to cover the development costs.'
		},
		{
			q: 'Do you offer discounts?',
			a: 'You get <strong>2-months off</strong> if you pay annually. In addition, we provide a 10% discount for non-profit organizations and early-stage startups. Contact us via live chat to get the coupon.'
		},
		{
			q: 'Who owns the content I write?',
			a: 'You own everything you write. You decide what to do with your content. You can export your content anytime and move to another platform.'
		},
		{
			q: 'Are there bandwidth/pageviews limitations?',
			a: "No, we don't limit the number of pageviews or bandwidth. Due to extensive caching and optimizations, we can handle a large number of pageviews without any issues."
		},
		{
			q: 'Can I display ads on my blog?',
			a: 'You decide! You are in control of your blog and the theme. You can add any ad code to your theme. You can easily add Google AdSense or any other ad network to your blog by adding the ad code to your blog/theme.'
		},
		{
			q: 'How to add a commenting system?',
			a: '<a href="https://talk.hyvor.com">Hyvor Talk</a> is available for free for all plans. You can also embed other commenting systems easily.'
		}
	];
</script>

<svelte:head>
	<title>Pricing - Hyvor Blogs</title>
</svelte:head>

<PricingPlans />
<SelfHost />
<Features />

<FeatureSectionTitle
	title="Frequently Asked Questions"
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
