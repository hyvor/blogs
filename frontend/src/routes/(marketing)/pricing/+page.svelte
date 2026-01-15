<script lang="ts">
	import { Link } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { PLANS, plansMax, plansStart } from './pricing';
	import FullTrialSignup from './../@components/FullTrialSignup.svelte';
	import type { Feature } from './pricing';
	import FeatureSectionTitle from '../@homepage/FeatureSectionTitle.svelte';
	import FeatureList from './FeatureList.svelte';
	import Plan from './Plan.svelte';
	import PlanSwitcher from './PlanSwitcher.svelte';
	import Faq from './Faq.svelte';
	import IconBrush from '@hyvor/icons/IconBrush';
	import IconCreditCard from '@hyvor/icons/IconCreditCard';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconPercent from '@hyvor/icons/IconPercent';
	import IconCCircle from '@hyvor/icons/IconCCircle';
	import IconSpeedometer2 from '@hyvor/icons/IconSpeedometer2';
	import IconChat from '@hyvor/icons/IconChat';
	import IconBadgeAd from '@hyvor/icons/IconBadgeAd';

	const basicFeatures: Feature[] = [
		{
			name: 'Users',
			description: 'Total number of users who writes for your blog (your team members)',
			values: [5, 15, 50]
		},
		{
			name: 'Storage',
			description: 'Total storage used for blog media (mostly uploaded images)',
			values: ['5GB', '150GB', '500GB']
		},
		{
			name: 'Custom Themes',
			description: 'Use default themes for free or build your own custom theme',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Custom Domain',
			description: 'Host your blog on your own domain',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Multi-language support',
			description: 'Add multiple languages to your blog and translate your posts',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Data Ownership',
			description:
				'You own everything you write. Export and move to another platform anytime.',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'No Branding',
			description: 'Remove Hyvor Blogs branding from your blog',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'SEO Analysis',
			description: 'In-post SEO analysis (check keywords, content, etc.)',
			values: [false, true, true, true, true, true]
		},
		{
			name: 'Link Analysis',
			description: 'Post link analysis, bi-weekly full-blog link analysis, and email reports',
			values: [false, true, true, true, true, true]
		}
	];

	const aiFeatures: Feature[] = [
		{
			name: 'GPT Writing',
			description:
				'Use OpenAI GPT 3.5 for content writing, keyword generation, and more. Usually, 1000 tokens is about 750 words.',
			values: [false, '100k tokens/m', '1m tokens/m']
		},
		{
			name: 'Auto-Translations',
			description:
				'Automatically translate your posts into multiple languages using DeepL. Monthly characters limit on each plan.',
			values: [false, '100k chars/m', '500k chars/m']
		}
	];

	const developerFeatures: Feature[] = [
		{
			name: 'Data API',
			description: 'Access public data of your blog via API',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Console API',
			description: 'The same API we use in our Console',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Delivery API',
			description: 'For self-serving a blog within Web Frameworks.',
			values: [true, true, true, true, true, true]
		},
		{
			name: 'Webhooks',
			description: 'Receive an HTTP request on events in your blog',
			values: [true, true, true, true, true, true]
		}
	];

	const integrations: Feature[] = [
		{
			name: 'Hyvor Talk',
			under: 'Commenting Platform',
			description: 'Add Hyvor Talk commenting system for FREE',
			values: ['25k credits/month (Premium)', '100k credits/month (Premium)', '250k credits/month (Business)']
		}
		// {
		// 	name: 'Hyvor Post',
		// 	under: 'Newsletter Platform',
		// 	description: 'Add Hyvor Post newsletter system for FREE',
		// 	values: [false, '25k emails/month', '100k emails/month']
		// }
	];

	function handleResize() {
		if (window.innerWidth < 1000) {
			plansMax.set(1);
		} else {
			plansMax.set(3);
		}
	}

	onMount(handleResize);
</script>

<svelte:head>
	<title>Pricing - Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/pricing" />
</svelte:head>

<svelte:window onresize={handleResize} />

<FeatureSectionTitle
	title="Simple & transparent pricing"
	subtitle="No hidden fees. Cancel anytime."
	wrapStyle="margin-top:60px"
/>

<div class="hds-container plans-wrap">
	<div class="top">
		<PlanSwitcher />
		<div class="plans">
			<div class="plans-left"></div>
			{#each PLANS as plan, i}
				{#if i >= $plansStart && i < $plansMax + $plansStart}
					<Plan name={plan.name} price={plan.price} />
				{/if}
			{/each}
		</div>
	</div>

	<div class="features">
		<FeatureList title="Basic Features" features={basicFeatures} />
		<FeatureList title="AI Features" features={aiFeatures} />
		<FeatureList title="Developer" features={developerFeatures} />
		<FeatureList title="Integrations" features={integrations} />
	</div>
</div>

<FeatureSectionTitle title="FAQs" />

<div class="faqs hds-container">
	<Faq q="How does the free trial work?" icon={IconHourglass}>
		Anyone can signup for the <strong>14-day free trial</strong> to test Hyvor Blogs. No credit card
		required. All features are available during the trial (except Hyvor Talk integration). After
		the trial, you can upgrade to a paid plan to continue using Hyvor Blogs.
	</Faq>

	<Faq q="Do I have to pay for themes?" icon={IconBrush}>
		No, all official themes are <strong
			>free and <Link href="https://github.com/hyvor/hyvor-blogs-themes" target="_blank"
				>open-source</Link
			></strong
		>. They can be easily installed on your blog with a few clicks. If you want to build your
		own theme, you will have to cover the development costs.
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
		theme. You can easily add Google AdSense or any other ad network to your blog by adding the
		ad code to your blog/theme.
	</Faq>

	<Faq q="How to add a commenting system?" icon={IconChat}>
		<Link href="https://talk.hyvor.com">Hyvor Talk</Link> is available for free on Starter and higher
		plans. You can also embed other commenting systems easily.
	</Faq>
</div>

<FullTrialSignup style="margin-top:130px" />

<style lang="scss">
	.plans {
		display: flex;
	}
	.plans-left {
		flex: 1;
	}

	@media (max-width: 1000px) {
		.plans-left {
			flex: 2;
		}
	}

	.top {
		position: sticky;
		top: var(--header-height);
		background-color: #fffaf8;
		z-index: 10;
		padding: 15px 0;
	}

	.plans-wrap {
		margin-top: 60px;
	}

	.features {
		margin: 20px 0;
	}

	.faqs {
		display: flex;
		flex-wrap: wrap;
		gap: 60px 20px;
		padding: 20px;
		margin-top: 40px;
	}
</style>
