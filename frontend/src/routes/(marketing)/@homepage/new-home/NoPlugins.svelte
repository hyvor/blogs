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

    let openItemId = $state("SEO");
    let selectedFeature = $state("SEO");

    function handleToggle(id: string) {
        if (openItemId === id) {
            // Do nothing – keep it open
            return;
        }

        // Open the new one
        openItemId = id;
        selectedFeature = id;
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

            <!-- Mobile image display - shown between title and accordions on mobile -->
            <div class="mobile-image">
                {#if Features.find(f => f.id === selectedFeature)?.image}
                    <Browser image={Features.find(f => f.id === selectedFeature)?.image} />
                {:else}
                    <div class="no-image-placeholder">
                        <span>Preview not available</span>
                    </div>
                {/if}
            </div>

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
                    <div class="no-image-placeholder">
                        <span>Image not available</span>
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
        overflow-x: hidden;
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
        color: white;
    }

    .section-content {
        display: flex;
        gap: 50px;
        padding: 0 55px;
        margin-bottom: 100px;
    }

    .left, .right {
        flex: 1;
    }

    .left {
        display: flex;
        flex-direction: column;
        gap: 15px;
        z-index: 2;
    }

    .right {
        justify-content: center;
        position: relative;
    }

    .img {
        transform: translateX(-400px); /* Start position */
        width: calc(100vw - 60%); /* Extend to viewport width minus left offset */
        max-width: none;
        margin-left: 0;
        padding-top: 100px;
        padding-left: 70px;
        border-radius: 20px;
        z-index: 0;
        position: absolute;
    }

    .img:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #483332;
        opacity: 0.6;
    }

    /* Hide mobile image on desktop */
    .mobile-image {
        display: none;
    }

    .no-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        padding: 50px;
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.6);
        border: 2px dashed rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.2);
    }

    /* Tablet styles */
    @media (max-width: 1024px) and (min-width: 769px) {
        .section-content {
            padding: 0 30px;
            gap: 30px;
        }

        .title {
            font-size: 2.2rem;
        }

        h3 {
            width: 100%;
            font-size: 18px;
            margin-bottom: 35px;
        }

        .img {
            transform: translateX(-300px);
            width: calc(100vw - 65%);
            padding-left: 50px;
            padding-top: 80px;
        }
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .all-in-one {
            padding: 40px 0;
        }

        .section-content {
            flex-direction: column;
            padding: 0 20px;
            gap: 0;
            margin-bottom: 60px;
        }

        .title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 12px;
        }

        h3 {
            width: 100%;
            font-size: 18px;
            margin-bottom: 25px;
            text-align: center;
            padding: 0 10px;
        }

        .left {
            gap: 12px;
        }

        /* Show mobile image */
        .mobile-image {
            display: block;
            margin-bottom: 25px;
            width: 100%;
        }

        .mobile-image .no-image-placeholder {
            height: 200px;
            font-size: 1rem;
            margin: 0 10px;
        }

        /* Hide desktop right section on mobile */
        .right {
            display: none;
        }

        /* Ensure mobile browser component is responsive */
        .mobile-image :global(.browser-content) {
            max-width: 100%;
        }
    }

    /* Small mobile devices */
    @media (max-width: 480px) {
        .all-in-one {
            padding: 30px 0;
        }

        .section-content {
            padding: 0 15px;
            margin-bottom: 40px;
        }

        .title {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .mobile-image {
            margin-bottom: 20px;
        }

        .mobile-image .no-image-placeholder {
            height: 150px;
            font-size: 0.9rem;
            margin: 0 5px;
        }

        .left {
            gap: 10px;
        }
    }
</style>
