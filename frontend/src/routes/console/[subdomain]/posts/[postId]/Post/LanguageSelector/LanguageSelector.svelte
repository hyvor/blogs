<script>
	import { languagesStore } from "../../../../../lib/stores";
	import LanguageTag from "./LanguageTag.svelte";
</script>

<div class="post-languages">

    {#each $languagesStore as lang (lang.id)}
        <LanguageTag language={lang} />
    {/each}

</div>
