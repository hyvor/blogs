<script lang="ts">
	import type { EditorView } from "prosemirror-view";
	import { postCurrentContentKey, postCurrentContentStore, postEditingStatusStore, postLanguageStore, postOriginalVariantStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
    import Prosemirror from "./Prosemirror.svelte";
	import PublishedOverlay from "./PublishedOverlay.svelte";
	import type { PostVariant } from "../../../../../../lib/types";
	import { handleEditorEventHandlers, type ProsemirrorEventDispatchType } from "./editorEvents";
    import { Editor } from '@hyvor/richtext';

    let uniqueKey = $derived(`${$postVariantStore.id}` +
        `-lang-${$postEditingStatusStore.languageId}` +
        `-key-${$postCurrentContentKey}` + 
        `-is-editing-published-${Number($postEditingStatusStore.isEditingPublished)}` +
        `-version-${$postEditingStatusStore.editorVersion}`);

    function handleChange(e: CustomEvent<string>) {

        const key = $postEditingStatusStore.isEditingPublished ? 'content_unsaved' : 'content';

        const updates = {
            [key]: e.detail
        } as Partial<PostVariant>;
       
        /* if (key === 'content_unsaved') {
            updates.content = e.detail;
        } */

        updatePostVariantStore(updates);
    }

    function handleView(e: CustomEvent<EditorView>) {
        updatePostEditingStatusValue('editorView', e.detail);
    }

    function handleEvent(e: CustomEvent<ProsemirrorEventDispatchType>) {
        handleEditorEventHandlers(e.detail.name, e.detail.event);
    }


</script>

<div class="editor hds-box">
    <EditorTop />

    {#key uniqueKey}
        <div class="wrap">
            <Editor 
                value={$postCurrentContentStore}
                onValueChange={handleChange}
                onDOMEvent={handleEvent}
                config={{
                    colorButtonBackground: '#5A8387',
                    colorButtonText: '#ffffff',
    
                    codeBlockEnabled: true,
                    codeBlockConfig: {
                        language: false,
                        fileName: false,
                        annotations: false,
                        annotationsUrl: null
                    },
    
                    customHtmlEnabled: true,
                    buttonEnabled: true,
    
                    // to be added later
                    tableEnabled: true,
                    bookmarkEnabled: true,
    
                    imageEnabled: true,
    
                    // does not make sense for emails (or email clients do not support)
                    tocEnabled: true,
                    audioEnabled: true,
                    embedEnabled: true,
    
                    fileMaxSizeInMB: 10,
                    fileUploader: async (file, name, type) => {
                        if (type !== 'image') {
                            return null;
                        }
                        const media = await uploadImage(file, 'issue_images');
                        return {
                            url: media.url
                        };
                    }
                }}
            />
            <PublishedOverlay />
        </div>
    {/key}
</div>

<style>
    
    .editor {
        position: relative;
    }
    .wrap {
        position: relative;
    }
</style>