<script lang="ts">
    import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';
    import IconSearchHeart from '@hyvor/icons/IconSearchHeart';
    import IconGlobe from '@hyvor/icons/IconGlobe';
    import IconPalette from '@hyvor/icons/IconPalette';
    import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
    import Browser from "./Browser.svelte";
    import BlogPage from './img/blog-page.png';
    import TestSS from './img/test-ss.png';
    import seoSS from './img/SEO.png';
    import seoFull from './img/seo-full.png';
    import Redirects from './img/redirects.png';
    import Redirect1 from './img/redirect-1.png';
    import Test from './img/test.png';

    import Accordion from "./@components/Accordion.svelte";

    let selectedFeature = $state("SEO");

    const Features = [
        {
            id: "SEO",
            name: "SEO",
            content: "Built-in SEO tools to help your blog rank higher on search engines without needing additional plugins.",
            link: "#seo-section",
            icon: IconSearchHeart,
            image: seoFull,
            alt: ""
        },
        {
            id: "Multi-Language",
            name: "Multi-Language",
            content: "Support for multiple languages, allowing you to reach a global audience with ease.",
            link: "",
            icon: IconGlobe,
            image: TestSS,
            alt: ""
        },
        {
            id: "Redirects",
            name: "Redirects",
            content: "Easily manage URL redirects to maintain SEO value and ensure a smooth user experience.",
            link: "",
            icon: IconSignTurnSlightRight,
            image: Redirect1,
            alt: ""
        },
        {
            id: "Custom Themes",
            name: "Custom Themes",
            content: "Choose from a variety of customizable themes to give your blog a unique look and feel.",
            link: "",
            icon: IconPalette,
            image: "",
            alt: ""
        },
        {
            id: "Custom Code",
            name: "Custom Code",
            content: "Add custom code snippets to enhance your blog's functionality and appearance.",
            link: "",
            icon: IconCodeSlash,
            image: "",
            alt: ""
        }
    ];

    let openItemId = $state<string | null>(null);

    function handleToggle(id: string) {
        // If the clicked item is already open, close it. Otherwise, open it.
        openItemId = openItemId === id ? null : id;

        // Update the selected feature when an accordion is opened
        if (openItemId === id) {
            selectedFeature = id;
        }
        // keeping the last selected feature when closing:
        // If needed image to disappear when accordion close
        else {
            selectedFeature = "";
        }
    }
</script>

<div class="all-in-one">
    <div class="section-content">
        <div class="left">
            <h2 class="title">
                Zero Plugin Headaches
            </h2>
            <h3>
                All essential blogging features built-in.
                Imagine WordPress and its most used plugins bundled into one, without the maintenance.
            </h3>
            {#each Features as feature (feature.name)}
                <div class="item">
                    <Accordion
                        title={feature.name}
                        content={feature.content}
                        icon={feature.icon}
                        id={feature.id}
                        link={feature.link}
                        isOpen={openItemId === feature.id}
                        onToggle={handleToggle}
                        style="cursor: pointer; border-radius: 10px; background-color: {selectedFeature === feature.id ? feature.color || '#f3f4f6' : 'transparent'}; border: {selectedFeature === feature.id ? '2px solid var(--accent-light)' : '2px solid transparent'}"
                    />
                </div>
            {/each}
        </div>

        <div class="right">
            <div class="img">
                {#if Features.find(f => f.id === selectedFeature)?.image}
                    <Browser image={Features.find(f => f.id === selectedFeature)?.image} />
                {:else}
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 50px; font-size: 1.2rem;">
                        Image not available
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>

<style>
    .all-in-one {
        margin: 0;
        padding: 60px 0;
        background-color: #483332;
    }

    .title {
        font-size: 2.5rem;
        color: var(--accent-text);
        font-weight: bold;
        margin-bottom: 15px;
    }

    h3 {
        font-weight: normal;
        width: 700px;
        margin-bottom: 45px;
        font-size: 20px;
        color: var(--gray-light);
    }

    .section-content {
        display: flex;
        gap: 50px;
        padding: 0 75px;
        margin-bottom: 100px;
    }

    .left, .right {
        flex: 1;
    }

    .left {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .right {
        position: relative;

    }

    .img {
        /*width: 150%;*/
        /*max-width: none;*/
        margin-left: 0;
        padding-top: 70px;
        padding-bottom: 70px;
        padding-left: 70px;
        border-radius: 20px;
    }

    .right .img :global(.browser-content){
        /*height: calc(100vh - 300px) !important;*/
    }

    @media (max-width: 992px) {
        .section-content {
            flex-direction: column;
            padding: 0 20px;
        }
        .title {
            text-align: center;
            padding-left: 0;
            margin: 30px;
        }
        .right {
            order: -1;
            margin-bottom: 30px;
        }
    }
</style>
