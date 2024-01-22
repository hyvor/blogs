<script lang="ts">
	import { onMount } from 'svelte';
	import { PLANS, plansMax, plansStart } from './pricing';
	import FullTrialSignup from './../@components/FullTrialSignup.svelte';
	import type { Feature } from './pricing';
	import FeatureSectionTitle from "../@homepage/FeatureSectionTitle.svelte";
	import FeatureList from "./FeatureList.svelte";
	import Plan from "./Plan.svelte";
	import PlanSwitcher from "./PlanSwitcher.svelte";

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


</style>