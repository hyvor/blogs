<script lang="ts">
    import {getDiffWordsCount} from "$lib/components/Diff/diff";
    import {getTextFromContent} from "$lib/Prosemirror/helpers";
    import {getWordsCount} from "../../../../../../../../lib/seo/words";
    import {postLanguageStore} from "../../../../../postStore";

    interface Props {
        contentOld: string | null;
        contentNew: string | null;
        diff: boolean;
    }

    let {contentOld, contentNew, diff}: Props = $props();

    let changedWords = $derived(getDiffWordsCount(
        getTextFromContent(contentOld),
        getTextFromContent(contentNew)
    ));

    let totalWords = $derived(getWordsCount(getTextFromContent(contentNew), $postLanguageStore.code));

</script>

<span>
    {#if diff}
        About <strong>{changedWords} word{changedWords === 1 ? '' : 's'}</strong> changed
    {:else}
        Total <strong>{totalWords} word{totalWords === 1 ? '' : 's'}</strong>
    {/if}   
</span>