<script lang="ts">
	import {
		postEditingStatusStore,
		postVariantStore,
		updatePostEditingStatusValue,
		updatePostVariantStore
	} from '../../../postStore';
	import EditorTop from './EditorTop/EditorTop.svelte';
	import PublishedOverlay from './PublishedOverlay.svelte';
	import type { PostVariant } from '../../../../../../lib/types';
	import { Editor } from '@hyvor/richtext';
	import { uploadMedia } from '../../../../tools/media/mediaActions';
	import type { EditorView } from 'prosemirror-view';
	import AutoTranslate from './EditorTop/AutoTranslate/AutoTranslate.svelte';

	let uniqueKey = $derived(
		`-is-editing-published-${Number($postEditingStatusStore.isEditingPublished)}` +
			`-version-${$postEditingStatusStore.editorVersion}`
	);

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

	function handleEvent(name: keyof HTMLElementEventMap, event: Event) {
		// handleEditorEventHandlers(name, event);
	}

	let editorView: EditorView = $state({} as EditorView);

	$effect(() => {
		if (editorView && editorView.state) {
			updatePostEditingStatusValue('editorView', editorView);
		}
	});
</script>

<div class="editor">
	<!-- <EditorTop /> -->

	{#key uniqueKey}
		<div class="wrap">
			<Editor
				bind:editorView
				value={null}
				onvaluechange={handleChange}
				ondomevent={handleEvent}
				config={{
					colorButtonBackground: '#5A8387',
					colorButtonText: '#ffffff',

					codeBlockEnabled: true,
					codeBlockConfig: {
						language: true,
						fileName: true,
						annotations: true,
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
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	.wrap {
		position: relative;
		flex: 1;
	}
</style>
