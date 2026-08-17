<script lang="ts">
	import {
		postCurrentContentKey,
		postEditingPublished,
		postEditor,
		postContentDirtyStore,
		postSuggestionModeStore,
		postVariantStore
	} from '../../../postStore';
	import { Editor, type Author, type CollabSendable, type CollabStepJSON, type CollabClientID } from '@hyvor/richtext';
	import wordCountPlugin from './plugins/plugin-wordcount';
	import { editorConfig, schema } from './editor';
	import { resolveAuthor, suggestionSource } from './suggestions';
	import { submitCollabSteps } from '../../../postActions';
	import { subscribeToCollabTopic, collabTopic } from './collab';
	import { authUserStore } from '../../../../../../lib/stores';

	// identifies this browser tab's editor session to the collab authority/other clients -
	// see editorConfig.collab and PostVariantCollabController on the backend
	const clientId = Math.random().toString(36).slice(2);

	let uniqueKey = $derived(`${$postVariantStore.id}-${$postCurrentContentKey}`);

	function handleChange() {
		$postContentDirtyStore = true;
	}

	function handleEvent(name: keyof HTMLElementEventMap, event: Event) {
		// handleEditorEventHandlers(name, event);
	}

	function handleSendable(sendable: CollabSendable) {
		submitCollabSteps({
			type: $postCurrentContentKey,
			version: sendable.version,
			steps: sendable.steps,
			client_id: String(sendable.clientID)
		}).catch((e) => console.error('Failed to submit collab steps', e));
	}

	let value = $derived(
		$postVariantStore[$postCurrentContentKey] ||
			JSON.stringify({ type: 'doc', content: [{ type: 'paragraph', content: [] }] })
	);

	// content_version/content_unsaved_version is the live collab version; the steps returned
	// alongside it are exactly the ones not yet reflected in `value` (see
	// PostVariantCollabService on the backend) - so the editor must start at the version
	// *before* those steps, then fast-forward via collab.receiveSteps() once mounted.
	let backlogSteps = $derived(
		($postCurrentContentKey === 'content'
			? $postVariantStore.content_steps
			: $postVariantStore.content_unsaved_steps) as CollabStepJSON[]
	);
	let backlogClientIds = $derived(
		($postCurrentContentKey === 'content'
			? $postVariantStore.content_client_ids
			: $postVariantStore.content_unsaved_client_ids) as CollabClientID[]
	);
	let liveVersion = $derived(
		$postCurrentContentKey === 'content'
			? $postVariantStore.content_version
			: $postVariantStore.content_unsaved_version
	);
	let initialVersion = $derived(liveVersion - backlogSteps.length);

	let isEditable = $derived($postVariantStore.status === 'draft' || $postEditingPublished);

	$effect(() => {
		$postEditor.setEditable(isEditable);
	});

	$effect(() => {
		const editor = $postEditor;
		if (!editor) return;

		if (backlogSteps.length > 0) {
			editor.collab.receiveSteps(backlogSteps, backlogClientIds);
		}

		const topic = collabTopic($postVariantStore.id, $postCurrentContentKey);
		return subscribeToCollabTopic(topic, (steps, clientIds) => {
			editor.collab.receiveSteps(steps, clientIds);
		});
	});

	// author is fixed for this editing session (the currently logged-in console user);
	// the mode changes are handled afterwards via postEditor.suggestions.setMode() from
	// the footer's SuggestionModeToggle, not by recreating this config - see postSuggestionModeStore
	let fullEditorConfig = $derived({
		...editorConfig,
		collab: {
			version: initialVersion,
			clientID: clientId,
			onSendable: handleSendable
		},
		suggestions: {
			author: `user:${$authUserStore.id}` as Author,
			mode: $postSuggestionModeStore,
			resolveAuthor,
			source: suggestionSource
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
