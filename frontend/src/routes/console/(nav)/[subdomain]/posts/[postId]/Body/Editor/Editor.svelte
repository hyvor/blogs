<script lang="ts">
	import { postCurrentContentKey, postCurrentContentStore, postEditingStatusStore, postLanguageStore, postOriginalVariantStore, postVariantStore, updatePostEditingStatusValue, updatePostVariantStore } from "../../../postStore";
	import EditorTop from "./EditorTop/EditorTop.svelte";
	import PublishedOverlay from "./PublishedOverlay.svelte";
	import type { PostVariant } from "../../../../../../lib/types";
	import { handleEditorEventHandlers } from "./editorEvents";
    import { Editor } from '@hyvor/richtext';
    import { uploadMedia } from "../../../../tools/media/mediaActions";

    let uniqueKey = $derived(`${$postVariantStore.id}` +
        `-lang-${$postEditingStatusStore.languageId}` +
        `-key-${$postCurrentContentKey}` + 
        `-is-editing-published-${Number($postEditingStatusStore.isEditingPublished)}` +
        `-version-${$postEditingStatusStore.editorVersion}`);

    function handleChange(value: string) {
        const parsed = JSON.parse(value);
        const key = $postEditingStatusStore.isEditingPublished ? 'content_unsaved' : 'content';

        const updates = {
            [key]: value
        } as Partial<PostVariant>;
       
        /* if (key === 'content_unsaved') {
            updates.content = e.detail;
        } */

        updatePostVariantStore(updates);
    }

    function handleView(e: CustomEvent<EditorView>) {
        updatePostEditingStatusValue('editorView', e.detail);
    }

    function handleEvent(name: keyof HTMLElementEventMap, event: Event) {
        handleEditorEventHandlers(name, event);
    }

    interface EditorView {
		focus(): void;
	}

	let editorView: EditorView & any = $state({} as any);

</script>

<div class="editor hds-box">
    <EditorTop />

    {#key uniqueKey}
        <div class="wrap">
            <Editor
                bind:editorView
                value={$postCurrentContentStore}
                onvaluechange={handleChange}
                ondomevent={handleEvent}
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
    
                    tableEnabled: true,
                    bookmarkEnabled: true,
    
                    imageEnabled: true,
    
                    tocEnabled: true,
                    audioEnabled: true,
                    embedEnabled: true,
    
                    fileMaxSizeInMB: 10,
                    fileUploader: async (file, name, type) => {
                        if (type !== 'image') {
                            return null;
                        }
                        const media = await uploadMedia(file, name);
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