<script lang="ts">
	import {
		postCurrentContentKey,
		postEditingPublished,
		postEditor,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import type { PostVariant } from '../../../../../../lib/types';
	import { Editor } from '@hyvor/richtext';
	import wordCountPlugin from './plugins/plugin-wordcount';
	import { editorConfig, schema } from './editor';

	let uniqueKey = $derived(`version-1`);

	function handleChange(value: string) {
		let key: 'content' | 'content_unsaved' = 'content';
		if ($postEditingPublished) {
			key = 'content_unsaved';
		}

		const updates = {
			[key]: value
		} as Partial<PostVariant>;

		updatePostVariantStore(updates);
	}

	function handleEvent(name: keyof HTMLElementEventMap, event: Event) {
		// handleEditorEventHandlers(name, event);
	}

	let value = $derived(
		$postVariantStore![$postCurrentContentKey] ||
			JSON.stringify({ type: 'doc', content: [{ type: 'paragraph', content: [] }] })
	);

	let isEditable = $derived($postVariantStore.status === 'draft' || $postEditingPublished);

	$effect(() => {
		$postEditor.setEditable(isEditable);
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
				editable={isEditable}
				{schema}
				{editorConfig}
				plugins={[wordCountPlugin()]}
			/>
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
