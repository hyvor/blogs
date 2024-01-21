<script>
	import CheckFeatures from "../../@components/CheckFeatures.svelte";
import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
	import Score from "./Score.svelte";
    import iconSeo from './seo.svg';
</script>

<FeatureSectionTitle 
    title="SEO & Speed"
    subtitle="All blogs are <b>automagically</b> optimized for SEO and speed. You don't need to worry about anything."
    icon={iconSeo}
/>

<div class="scores">
    <Score name="Performance" score={100} />
    <Score name="Accessibility" score={100} />
    <Score name="Best Practices" score={100} />
    <Score name="SEO" score={100} />
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

<style lang="scss">
    .scores {
        width: 600px;
        margin: 60px auto;
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
</style>