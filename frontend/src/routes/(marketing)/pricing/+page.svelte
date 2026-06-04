<script>
	import { Link } from '@hyvor/design/components';
	import FullTrialSignup from './../@components/FullTrialSignup.svelte';
	import FeatureSectionTitle from '../@homepage/FeatureSectionTitle.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import Features from './Features.svelte';
	import Faq from './Faq.svelte';
	import IconBrush from '@hyvor/icons/IconBrush';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconPercent from '@hyvor/icons/IconPercent';
	import IconCCircle from '@hyvor/icons/IconCCircle';
	import IconSpeedometer2 from '@hyvor/icons/IconSpeedometer2';
	import IconChat from '@hyvor/icons/IconChat';
	import IconBadgeAd from '@hyvor/icons/IconBadgeAd';
	import SelfHost from './SelfHost.svelte';
</script>

<svelte:head>
	<title>Pricing - Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/pricing" />
</svelte:head>

<div class="hds-container">
	<FeatureSectionTitle
		title="Simple & transparent pricing"
		subtitle="No hidden fees. Cancel anytime."
		wrapStyle="margin-top:50px"
	/>
</div>

<PricingPlans />
<SelfHost />
<Features />

<FeatureSectionTitle title="FAQs" />

<div class="faqs hds-container">
	<Faq q="How does the free trial work?" icon={IconHourglass}>
		Anyone can signup for the <strong>14-day free trial</strong> to test Hyvor Blogs. No credit card required.
		All features are available during the trial (except Hyvor Talk integration). After the trial, you
		can upgrade to a paid plan to continue using Hyvor Blogs.
	</Faq>

	<Faq q="Do I have to pay for themes?" icon={IconBrush}>
		No, all official themes are <strong
			>free and <Link href="https://github.com/hyvor/hyvor-blogs-themes" target="_blank"
				>open-source</Link
			></strong
		>. They can be easily installed on your blog with a few clicks. If you want to build your own
		theme, you will have to cover the development costs.
	</Faq>

	<Faq q="Do you offer discounts?" icon={IconPercent}>
		You get <strong>2-months off</strong> if you pay annually. In addition, we provide a 10% discount
		for non-profit organizations and early-stage startups. Contact us via live chat to get the coupon.
	</Faq>

	<Faq q="Who owns the content I write?" icon={IconCCircle}>
		You own everything you write. You decide what to do with your content. You can export your
		content anytime and move to another platform.
	</Faq>

	<Faq q="Are there bandwidth/pageviews limitations?" icon={IconSpeedometer2}>
		No, we don't limit the number of pageviews or bandwidth. Due to extensive caching and
		optimizations, we can handle a large number of pageviews without any issues.
	</Faq>

	<Faq q="Can I display ads on my blog?" icon={IconBadgeAd}>
		You decide! You are in control of your blog and the theme. You can add any ad code to your
		theme. You can easily add Google AdSense or any other ad network to your blog by adding the ad
		code to your blog/theme.
	</Faq>

	<Faq q="How to add a commenting system?" icon={IconChat}>
		<Link href="https://talk.hyvor.com">Hyvor Talk</Link> is available for free for all plans. You can
		also embed other commenting systems easily.
	</Faq>
</div>

<FullTrialSignup style="margin-top:130px" />

<style>
	.faqs {
		display: flex;
		flex-wrap: wrap;
		gap: 60px 20px;
		padding: 20px;
		margin-top: 40px;
		margin-bottom: 40px;
	}
</style>
