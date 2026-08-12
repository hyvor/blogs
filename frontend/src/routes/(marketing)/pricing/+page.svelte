<script>
	import { Link } from '@hyvor/design/components';
	import FullTrialSignup from './../@components/FullTrialSignup.svelte';
	import FeatureSectionTitle from '../@homepage/FeatureSectionTitle.svelte';
	import PricingPlans from './PricingPlans.svelte';
	import Features from './Features.svelte';
	import Faq from './Faq.svelte';
	import SelfHost from './SelfHost.svelte';
</script>

<svelte:head>
	<title>Pricing - Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/pricing" />
</svelte:head>

<PricingPlans />
<SelfHost />
<Features />

<FeatureSectionTitle
	title="Frequently Asked Questions"
	subtitle="Common questions about pricing, billing, and features."
	h2Style="font-family: var(--font-serif); font-weight: 700; letter-spacing: -0.01em;"
/>

<div class="faqs hds-container">
	<div class="faq-col">
		<Faq q="How does the free trial work?" open>
			Anyone can signup for the <strong>14-day free trial</strong> to test Hyvor Blogs. No credit card
			required. All features are available during the trial (except Hyvor Talk integration). After the
			trial, you can upgrade to a paid plan to continue using Hyvor Blogs.
		</Faq>

		<Faq q="Do I have to pay for themes?">
			No, all official themes are <strong
				>free and <Link href="https://github.com/hyvor/hyvor-blogs-themes" target="_blank"
					>open-source</Link
				></strong
			>. They can be easily installed on your blog with a few clicks. If you want to build your own
			theme, you will have to cover the development costs.
		</Faq>

		<Faq q="Do you offer discounts?">
			You get <strong>2-months off</strong> if you pay annually. In addition, we provide a 10% discount
			for non-profit organizations and early-stage startups. Contact us via live chat to get the coupon.
		</Faq>

		<Faq q="Who owns the content I write?">
			You own everything you write. You decide what to do with your content. You can export your
			content anytime and move to another platform.
		</Faq>
	</div>

	<div class="faq-col">
		<Faq q="Are there bandwidth/pageviews limitations?">
			No, we don't limit the number of pageviews or bandwidth. Due to extensive caching and
			optimizations, we can handle a large number of pageviews without any issues.
		</Faq>

		<Faq q="Can I display ads on my blog?">
			You decide! You are in control of your blog and the theme. You can add any ad code to your
			theme. You can easily add Google AdSense or any other ad network to your blog by adding the ad
			code to your blog/theme.
		</Faq>

		<Faq q="How to add a commenting system?">
			<Link href="https://talk.hyvor.com">Hyvor Talk</Link> is available for free for all plans. You can
			also embed other commenting systems easily.
		</Faq>
	</div>
</div>

<FullTrialSignup style="margin-top:130px" />

<style>
	.faqs {
		display: flex;
		gap: 0 60px;
		padding: 20px;
		margin-top: 20px;
		margin-bottom: 40px;
		/* .hds-container already caps this at 100% of the viewport — cap at
		   1100px on top of that without clobbering the 100% (a bare
		   max-width:1100px here would win over hds-container's max-width:100%
		   by specificity and break shrinking on mobile) */
		max-width: min(1100px, 100%);
		margin-left: auto;
		margin-right: auto;
	}

	.faq-col {
		flex: 1;
		min-width: 0;
	}

	@media (max-width: 768px) {
		.faqs {
			flex-direction: column;
			gap: 0;
		}
	}
</style>
