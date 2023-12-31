<script>
	import { languagesStore } from "../../../../../lib/stores/languagesStore";
	import { postEditingStatusStore } from "../../../postStore";
	import LanguageTag from "./LanguageTag.svelte";
</script>

<div class="post-languages">

    {#each $languagesStore as lang (lang.id)}
        <LanguageTag 
            language={lang}
            active={lang.id === $postEditingStatusStore.languageId}
            isCreating={true}
        />
    {/each}

</div>
