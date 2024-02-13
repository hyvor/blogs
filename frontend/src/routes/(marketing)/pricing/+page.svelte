<script lang="ts">
	import { Link } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { PLANS, plansMax, plansStart } from './pricing';
	import FullTrialSignup from './../@components/FullTrialSignup.svelte';
	import type { Feature } from './pricing';
	import FeatureSectionTitle from "../@homepage/FeatureSectionTitle.svelte";
	import FeatureList from "./FeatureList.svelte";
	import Plan from "./Plan.svelte";
	import PlanSwitcher from "./PlanSwitcher.svelte";
	import Faq from "./Faq.svelte";
	import { IconBrush, IconCreditCard, IconHourglass, IconPercent, IconCCircle, IconSpeedometer2, IconChat, IconBadgeAd } from "@hyvor/icons";

    const basicFeatures : Feature[] = [
        {
            name: 'Users',
            description: 'Total number of users who writes for your blog (your team members)',
            values: [2, 5, 15, 100, 1000, 'Unlimited']
        },
        {
            name: 'Storage',
            description: 'Total storage used for blog media (mostly uploaded images)',
            values: ['1GB', '40GB', '250GB', '1TB', '2TB', '5TB']
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
            description: 'You own everything you write. Export and move to another platform anytime.',
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
        },
    ];

    const aiFeatures : Feature[] = [
        {
            name: 'GPT Writing',
            description: 'Use OpenAI GPT 3.5 for content writing, keyword generation, and more. Usually, 1000 tokens is about 750 words.',
            values: [false, '100k tokens/m', '1m tokens/m', '3m tokens/m', '15m tokens/m', '30m tokens/m']
        },
        {
            name: 'Auto-Translations',
            description: 'Automatically translate your posts into multiple languages using DeepL. Monthly characters limit on each plan.',
            values: [false, '100k chars/m', '300k chars/m', '1m chars/m', '5m chars/m', '15m chars/m']
        },
    ]

    const developerFeatures : Feature[] = [
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
        },
    ];

    const integrations = [
        {
            name: 'Hyvor Talk Comments',
            description: 'Add Hyvor Talk commenting system for FREE',
            values: [false, true, true, true, true, true]
        }
    ]

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
    <link rel="canonical" href="https://blogs.hyvor.com/pricing">
</svelte:head>

<svelte:window on:resize={handleResize} />

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

<FeatureSectionTitle 
    title="FAQs"
/>

<div class="faqs hds-container">

    <Faq q="How does the free trial work?" icon={IconHourglass}>
        Anyone can signup for the <strong>7-day free trial</strong> to test Hyvor Blogs. No credit card required. 
        All features are available during the trial (except Hyvor Talk integration). After the trial, you can upgrade to a paid plan to continue using Hyvor Blogs. Each blog needs a separate subscription.
    </Faq>

    <Faq q="Do I have to pay for themes?" icon={IconBrush}>
        No, all official themes are <strong>free and open-source</strong>. They can be easily installed on your blog with a few clicks. If you want to build your own theme, you will have to cover the development costs.
    </Faq>

    <Faq q="Do you offer discounts?" icon={IconPercent}>
        You get <strong>2-months off</strong> if you pay annually. In addition, we provide a 10% discount for non-profit organizations and early-stage startups. Contact us via live chat to get the coupon.
    </Faq>

    <Faq q="How do payments work?" icon={IconCreditCard}>
        Payments are processed securely through our Merchant of Record, <Link href="https://paddle.net" rel="nofollow" target="_blank">Paddle</Link>. We support cards and Paypal in multiple currencies. Paddle will handle all the tax calculations and payments.
    </Faq>

    <Faq q="Who owns the content I write?" icon={IconCCircle}>
        You own everything you write. You decide what to do with your content. You can export your content anytime and move to another platform.
    </Faq>

    <Faq q="Are there bandwidth/pageviews limitations?" icon={IconSpeedometer2}>
        No, we don't limit the number of pageviews or bandwidth. Due to extensive caching and optimizations, we can handle a large number of pageviews without any issues.
    </Faq>

    <Faq q="Can I display ads on my blog?" icon={IconBadgeAd}>
        You decide! You are in control of your blog and the theme. You can add any ad code to your theme. You can easily add Google AdSense or any other ad network to your blog by adding the ad code to your blog/theme.
    </Faq>

    <Faq q="How to add a commenting system?" icon={IconChat}>
        <Link href="https://talk.hyvor.com">Hyvor Talk</Link> is available for free on Growth and higher plans. You can also embed other commenting systems easily.
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
        margin-top:40px;
    }


</style>