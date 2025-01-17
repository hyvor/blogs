<script lang="ts">
	import { getDiffWordsCount } from "$lib/components/Diff/diff";
	import { getTextFromContent } from "../../../../../../../../lib/prosemirror/helpers";
	import { getWordsCount } from "../../../../../../../../lib/seo/words";
	import { postLanguageStore } from "../../../../../postStore";

    export let contentOld: string | null;
    export let contentNew: string | null;
    export let diff: boolean;

    $: changedWords = getDiffWordsCount(
        getTextFromContent(contentOld),
        getTextFromContent(contentNew)
    );

    $: totalWords = getWordsCount(getTextFromContent(contentNew), $postLanguageStore.code);

</script>

<span>
    {#if diff}
        About <strong>{changedWords} word{changedWords === 1 ? '' : 's'}</strong> changed
    {:else}
        Total <strong>{totalWords} word{totalWords === 1 ? '' : 's'}</strong>
    {/if}   
</span>