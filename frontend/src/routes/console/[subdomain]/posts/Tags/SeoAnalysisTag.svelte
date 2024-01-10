<script lang="ts">
	import type { PostVariant } from "../../../lib/types";
	import SeoScoreTag from "../[postId]/Sidebar/Seo/SeoScoreTag.svelte";
	import { analyzePostVariant } from "../seoStore";
    export let postVariant : PostVariant;
    const seoAnalysis = analyzePostVariant(postVariant);
</script>

<SeoScoreTag score={seoAnalysis.average} percentage />