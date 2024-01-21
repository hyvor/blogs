<script>
	import CustomThemes from './@homepage/CustomThemes/CustomThemes.svelte';
	import Reviews from './@components/Reviews.svelte';
	import AboveFold from './@homepage/AboveFold.svelte';
	import Hosting from './@homepage/Hosting/Hosting.svelte';
	import Seo from './@homepage/Seo/Seo.svelte';
</script>

<AboveFold />
<Reviews />

<!-- <div class="all hds-container">
    <h2>
        All-in-one!
    </h2>
    <h3>
        EVERYTHING you need to run a blog is here. No more switching between tools or installing plugins that break your site!
    </h3>
</div> -->

<CustomThemes />
<Hosting />
<Seo />

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