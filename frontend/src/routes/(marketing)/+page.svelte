<script>
	import CustomThemes from './@homepage/CustomThemes/CustomThemes.svelte';
	import Reviews from './@components/Reviews.svelte';
	import AboveFold from './@homepage/AboveFold.svelte';
	import Hosting from './@homepage/Hosting/Hosting.svelte';
	import Seo from './@homepage/Seo/Seo.svelte';
	import MultiLanguage from './@homepage/MultiLanguage/MultiLanguage.svelte';
</script>

<svelte:head>
    <title>Hyvor Blogs - All-in-one Blogging Platform</title>
    <meta name="description" content="Hyvor Blogs is a powerful blogging platform that allows you to create a blog, customize it, and start publishing articles instantly. In-built SEO, multi-language support, custom domains with SSL, and many more features included!">
</svelte:head>

<AboveFold />
<Reviews />
<CustomThemes />
<Hosting />
<Seo />
<MultiLanguage />

<style lang="scss">
    .all {
        padding: 25px 0;
        text-align: center;
        h2 {
            font-size: 38px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 20px;
            width: 650px;
            margin: 0 auto;
            font-weight: normal;
            line-height: 1.4;
        }
    }
</style>