<script>
	import { IconGear, IconGlobe, IconPencil, IconPlusCircle, IconTranslate } from "@hyvor/icons";
	import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
	import Step from "./Step.svelte";

    import icon from './multi-language.svg';
</script>

<FeatureSectionTitle 
    title="Multi-language"
    subtitle="Posts, author names, tags, metadata, ...everything is easily translatable. RTL support too!"
    icon={icon}
/>


<div class="wrap">
    <div class="inner">
        <Step 
            icon={IconPlusCircle}
            number={1}
            title="Add a language"
            description="Add new language with name and code. This will make everything ready for translation. You can add more languages anytime."
        />
        <Step
            icon={IconTranslate}
            number={2}
            title="Translate posts and data"
            description="Start translating posts manually or using the integrated AI translator. And, translate blog data like author names, tags, etc."
        />
        <Step 
            icon={IconGlobe}
            number={3}
            title="Reach global audience!"
            description="
                Now, your blog is ready to reach a global audience. Hyvor Blogs will automatically handle the technical parts for you!
            "
        ></Step>
    </div>
</div>

<style>

    .wrap {
        margin: 60px 0;
        padding: 60px 20px;
        background-color: var(--blue-light);
    }

    .inner {
        display: flex;
        justify-content: space-between;
        width: 1200px;
        padding: 0 15px;
        margin: 0 auto;
        max-width: 100%;
    }

</style>