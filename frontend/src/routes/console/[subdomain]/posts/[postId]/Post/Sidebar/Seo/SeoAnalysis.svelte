<script lang="ts">
	import { variantSeoStore } from "../../../../../../lib/stores/seoStore";
	import SeoCurve from "./SeoCurve.svelte";
	import SeoResults from "./SeoResults.svelte";
</script>

<div class="seo-top">
    <SeoCurve />
</div>

<SeoResults />

<style>
    .seo-top {
        display: flex;
    }
</style>