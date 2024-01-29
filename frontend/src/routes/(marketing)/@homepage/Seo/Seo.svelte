<script>
	import CheckFeatures from "../../@components/CheckFeatures.svelte";
    import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
	import Score from "./Score.svelte";
    import iconSeo from './seo.svg';

    import seoAnalyzerMov from './seo-analyzer.mov';
    import linkAnalyzerMov from './link-analyzer.mov';
    import linkAnalyzerSettingsPng from './link-analyzer-settings.png';
</script>

<FeatureSectionTitle 
    title="SEO & Speed"
    subtitle="All blogs are <b>automagically</b> optimized for SEO and speed. You don't need to worry about anything."
    icon={iconSeo}
/>

<div class="scores">
    <div class="scores-inner">
        <Score name="Performance" score={100} />
        <Score name="Accessibility" score={100} />
        <Score name="Best Practices" score={100} />
        <Score name="SEO" score={100} />
    </div>
</div>

<div class="features hds-container">

    <div class="features-one">

        <div class="feature-title">
            SEO
        </div>

        <!-- SEO -->
        <CheckFeatures features={[
            'Meta tags (SEO, Open Graph, Twitter Cards, etc.)',
            'Automatic sitemaps',
            'Robots.txt',
            'Canonical URLs',
            'Configurable settings',
            'In-built SEO analyzer',
            'In-built link analyzer to prevent broken links',
        ]} />

    </div>


    <div class="features-one">

        <div class="feature-title">
            Speed
        </div>

        <!-- Speed -->
        <CheckFeatures features={[
            'Static, cached HTML pages',
            'No Javascript bloat',
            'Automatic WebP conversion',
            'Responsive images',
            'Flashload for faster navigation'
        ]} />

    </div>

</div>

<div class="analyzers hds-container">

    <div class="analyzer">

        <div class="title">
            SEO Analyzer
        </div>

        <div class="subtitle">
            Our in-built SEO analyzer will help you optimize your posts for search engines and prevent you from making common SEO mistakes. You will receive <strong>realtime feedback</strong> as you write your blog post.
        </div>

        <div class="hds-box video-wrap">
            <video muted controls>
                <source src={seoAnalyzerMov} type="video/mp4">
                <track kind="captions">
            </video>
        </div>

    </div>

    <div class="analyzer">

        <div class="title">
            Post Link Analyzer
        </div>

        <div class="subtitle">
            Remember that broken link you added to your blog post? That won't happen again with our link analyzer.
        </div>

        <div class="hds-box video-wrap">
            <video muted controls>
                <source src={linkAnalyzerMov} type="video/mp4">
                <track kind="captions">
            </video>
        </div>

    </div>

</div>

<div class="analyzer">

    <div class="title">
        Full-blog Link Analyzer
    </div>

    <div class="subtitle">
        What about links that break over time? No worries! We run a full-blog link analysis every two weeks to notify you of any broken or redirect links.
    </div>

    <div class="img-wrap">
        <img 
            src={linkAnalyzerSettingsPng} 
            alt="Link Analyzer Settings"
        >
    </div>

</div>

<style lang="scss">
    .scores {
        margin: 60px auto;
        background-color: #f1f6f1;
        padding: 40px 0;
    }

    :global(:root.dark) .scores {
        background-color: #191e19;
    }

    .scores-inner {
        width: 700px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
    }
    .features {
        display: flex;
        gap: 15px;
    }
    .features-one {
        flex: 1;
    }
    .feature-title {
        font-weight: 600;
        margin-top: 30px;
        font-size: 18px;
    }

    .analyzer {

        margin-top: 50px;

        .title {
            font-weight: 600;
            font-size: 28px;
            margin-bottom: 10px;
            text-align: center;
        }

        .subtitle {
            color: var(--text-light);
            width: 700px;
            max-width: 100%;
            margin: 0 auto;
            text-align: center;
            font-size: 18px;
        }

        video {
            width: 100%;
            margin: 0 auto;
            display: block;
        }

        .video-wrap {
            border-radius: 20px;
            overflow: hidden;
            margin-top: 20px;
        }

        img {
            width: 750px;
            max-width: 100%;
            margin: 40px auto;
            display: block;
        }

    }

</style>