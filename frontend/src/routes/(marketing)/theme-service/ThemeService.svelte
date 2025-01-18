<script>
	import IconBoxSeam from '@hyvor/icons/IconBoxSeam';
import IconCodeSlash from '@hyvor/icons/IconCodeSlash';
import IconEye from '@hyvor/icons/IconEye';

	import Step from "../@homepage/MultiLanguage/Step.svelte";
</script>




<div class="wrap">
    <div class="inner">
        <Step 
            icon={IconEye}
            number={1}
            title="Tell Us Your Vision!"
            description="Whether you have a detailed design in mind or just a broad idea, our theme developement experts work closely with you to understand your requirements."
        />
        <Step
            icon={IconCodeSlash}
            number={2}
            title="Development Process"
            description="Our officially partnered developers will then craft a custom blog theme tailored specifically to your needs within 1-2 weeks."
        />
        <Step 
            icon={IconBoxSeam}
            number={3}
            title="Delivery"
            description="Once the theme is ready, we'll provide you with all the necessary files and instructions to seamlessly integrate it into your Hyvor blog."
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

    @media (max-width:992px) {
        .inner {
            flex-direction: column;
        }
    }

</style>