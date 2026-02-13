<script lang="ts">
	import Keywords from './Keywords/Keywords.svelte';
	import SeoCurve from './SeoCurve.svelte';
	import SeoResults from './SeoResults.svelte';
</script>

<div class="seo-top">
	<SeoCurve />
	<Keywords />
</div>

<SeoResults />

<style>
	.seo-top {
		display: flex;
	}
</style>
