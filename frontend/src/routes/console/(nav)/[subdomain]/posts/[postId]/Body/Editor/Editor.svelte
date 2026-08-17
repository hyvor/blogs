<script lang="ts">
	import {
		postCurrentContentKey,
		postEditingPublished,
		postEditor,
		postSuggestionModeStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import type { PostVariant } from '../../../../../../lib/types';
	import { authUserStore } from '../../../../../../lib/stores';
	import { Editor, type Author } from '@hyvor/richtext';
	import wordCountPlugin from './plugins/plugin-wordcount';
	import { editorConfig, schema } from './editor';
	import { resolveAuthor, suggestionSource } from './suggestions';

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

	// author is fixed for this editing session (the currently logged-in console user);
	// the mode changes are handled afterwards via postEditor.suggestions.setMode() from
	// the footer's SuggestionModeToggle, not by recreating this config - see postSuggestionModeStore
	const fullEditorConfig = {
		...editorConfig,
		suggestions: {
			author: `user:${$authUserStore.id}` as Author,
			mode: $postSuggestionModeStore,
			resolveAuthor,
			source: suggestionSource
		}
	};
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
				editorConfig={fullEditorConfig}
				plugins={[wordCountPlugin()]}
			/>
		</div>
	{/key}
</div>

<style>
	@import url(https://fonts.bunny.net/css?family=source-serif-4:400,600);
	.editor {
		position: relative;
		flex: 1;
		display: flex;
		flex-direction: column;
		/* font-family: 'Source Serif 4', sans-serif; */
	}
	.wrap {
		position: relative;
		flex: 1;
	}
</style>
