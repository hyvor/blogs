<script lang="ts">
	import {
		postCurrentContentKey,
		postEditor,
		postVariantStore,
		updatePostEditingStatusValue,
		updatePostVariantStore
	} from '../../../postStore';
	import PublishedOverlay from './PublishedOverlay.svelte';
	import type { PostVariant } from '../../../../../../lib/types';
	import { Editor } from '@hyvor/richtext';
	import { uploadMedia } from '../../../../tools/media/mediaActions';
	import type { EditorView } from 'prosemirror-view';

	let uniqueKey = $derived(`version-1`);

	function handleChange(value: string) {
		const parsed = JSON.parse(value);
		const key = $postCurrentContentKey;

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
	let value = $derived(
		$postVariantStore![$postCurrentContentKey] ||
			JSON.stringify({ type: 'doc', content: [{ type: 'paragraph', content: [] }] })
	);

	$effect(() => {
		if (editorView && editorView.state) {
			// updatePostEditingStatusValue('editorView', editorView);
		}
	});
</script>

<div class="editor">
	{#key uniqueKey}
		<div class="wrap">
			<Editor
				bind:this={$postEditor}
				{value}
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
