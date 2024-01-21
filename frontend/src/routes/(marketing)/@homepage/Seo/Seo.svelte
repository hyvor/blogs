<script>
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

<style lang="scss">
    .scores {
        width: 600px;
        margin: 40px auto;
        display: flex;
        justify-content: space-between;
    }
</style>