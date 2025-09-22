<script>
	import { Button, Link, Loader } from '@hyvor/design/components';
	import ThemesPreview from '../../@components/ThemesPreview.svelte';
	// import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	// import FeatureSectionTitle from '../FeatureSectionTitle.svelte';
	// import logo from './custom-themes.svg';

	let isLoading = $state(true);
</script>


<div class="wrapper">
    <div class="title">
        <h1>
            Custom Themes
        </h1>
        <h2>
            Match your blog to your brand with custom themes. Choose from a variety of themes or create your own.
        </h2>
    </div>
    <div class="preview hds-container">
        <ThemesPreview on:load={() => (isLoading = false)} lockScroll={true} />

        {#if isLoading}
            <Loader full />
        {/if}
    </div>

    <div class="action">
        <Button as="a" href="/themes">Go to themes page &rarr;</Button>
    </div>

<!--    <div class="dev">-->
<!--        Are you familiar with HTML and CSS? Have a web developer in your team? You can create your own-->
<!--        theme from scratch!-->
<!--        <Link href="https://blogs.hyvor.com/docs/themes-overview" target="_blank">-->
<!--            Theme Development Docs-->
<!--            {#snippet end()}-->
<!--                <IconBoxArrowUpRight size={12} />-->
<!--            {/snippet}-->
<!--        </Link>-->
<!--    </div>-->
</div>

<style>

    .wrapper {
        background-color: #3c3030;
       padding: 50px 0;
    }

    .title {
        margin-inline-start: 55px;
    }

    h1 {
        margin: 0;
        font-size:60px;
        text-align: start;
        color: white;
    }

    h2 {

        font-weight: normal;
        font-size: 20px;
        color: var(--gray-light);
        margin: 0;
        padding-top: 20px;
        text-align: start;
    }
	.preview {
		margin-top: 50px;
        margin-left: 55px;
        margin-right: 55px;
		/*width: 1300px;*/
        width: auto;
		height: 600px;
        padding: 0 !important;
	}
	.action {
		margin-top: 50px;
		margin-bottom: 30px;
		text-align: center;
        color: white;
	}

	.dev {
		text-align: center;
		color: var(--gray-light);
		width: 500px;
		max-width: 100%;
		margin: auto;
        padding-bottom: 100px;

	}
</style>
