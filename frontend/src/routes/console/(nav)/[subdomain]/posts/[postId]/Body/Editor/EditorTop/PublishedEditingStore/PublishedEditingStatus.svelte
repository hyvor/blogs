<script lang="ts">
	import { confirm } from "@hyvor/design/components";
    import { postEditingStatusStore, postVariantStore, updatePostEditingStatusValue } from "../../../../../postStore";
	import { getDiffWordsCount } from "$lib/components/Diff/diff";
	import { getTextFromContent } from "../../../../../../../../lib/prosemirror/helpers";
	import ConfirmModal from "./ConfirmModal.svelte";
	import { updatePostVariant } from "../../../../../postActions";

    async function handleClick() {

        const wordsCount = getDiffWordsCount(
            getTextFromContent($postVariantStore.content),
            getTextFromContent($postVariantStore.content_unsaved)
        );

        if (await confirm({
            title: 'Discard changes?',
            content: ConfirmModal,
            contentProps: {words: wordsCount},
            danger: true,
            confirmText: 'Yes, discard changes'
        })) {
            updatePostEditingStatusValue('isEditingPublished', false);
            updatePostVariant({content_unsaved: null}, true);
        }
    }
</script>

{#if $postEditingStatusStore.isEditingPublished}

    <span>
        Editing published. <button onclick={handleClick}>Discard</button>
    </span>

{/if}

<style>
    span {
        font-size: 13px;
        color: var(--red-dark);
    }
    button {
        text-decoration: underline;
    }
</style>