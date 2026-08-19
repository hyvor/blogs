<script lang="ts">
	import {
		postCurrentContentKey,
		postEditingPublished,
		postEditor,
		postContentDirtyStore,
		postSuggestionModeStore,
		postVariantStore
	} from '../../../postStore';
	import {
		Editor,
		type Author,
		type CollabSendable,
		type CollabStepJSON,
		type CollabClientID,
		type RemoteCursor
	} from '@hyvor/richtext';
	import wordCountPlugin from './plugins/plugin-wordcount';
	import { editorConfig, schema } from './editor';
	import { resolveAuthor, suggestionSource } from './suggestions';
	import { submitCollabSteps, submitCollabCursor, syncCollabSteps } from '../../../postActions';
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

	async function handleSendable(sendable: CollabSendable) {
		try {
			const response = await submitCollabSteps({
				type: $postCurrentContentKey,
				version: sendable.version,
				steps: sendable.steps,
				client_id: String(sendable.clientID)
			});

			// stale - the response carries exactly the steps we're missing (see
			// PostVariantCollabService::submitSteps), straight from post_variant_steps rather
			// than waiting on Mercure to somehow redeliver them. Applying them rebases our
			// still-pending steps, and prosemirror-collab automatically re-fires onSendable
			// with the rebased batch - no manual retry needed here.
			if (!response.accepted) {
				$postEditor?.collab.receiveSteps(
					response.steps as CollabStepJSON[],
					response.client_ids as CollabClientID[]
				);
			}
		} catch (e) {
			console.error('Failed to submit collab steps', e);
		}
	}

	function handleLocalCursorChange(cursor: { from: number; to: number } | null) {
		submitCollabCursor({
			type: $postCurrentContentKey,
			client_id: clientId,
			cursor
		}).catch((e) => console.error('Failed to submit collab cursor', e));
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

		// other users' cursors for this topic - keyed by clientId, rebuilt fresh per
		// subscription since a topic change (variant/content-type switch) makes any prior
		// roster stale
		const cursors = new Map<string, RemoteCursor>();

		// called when the Mercure EventSource reconnects after a drop - whatever was published
		// while we were disconnected is gone from that transport's point of view (no replay), so
		// pull the durable truth directly instead of just hoping nothing was missed
		async function catchUp() {
			try {
				const response = await syncCollabSteps({
					type: $postCurrentContentKey,
					version: editor.collab.getVersion()
				});
				if (response.steps.length > 0) {
					editor.collab.receiveSteps(
						response.steps as CollabStepJSON[],
						response.client_ids as CollabClientID[]
					);
				}
			} catch (e) {
				console.error('Failed to sync collab steps', e);
			}
		}

		const topic = collabTopic($postVariantStore.id, $postCurrentContentKey);
		return subscribeToCollabTopic(
			topic,
			(steps, clientIds) => {
				editor.collab.receiveSteps(steps, clientIds);
			},
			(message) => {
				if (message.client_id === clientId) return; // ignore our own echo, if any

				if (message.clear || !message.user || message.from === undefined || message.to === undefined) {
					cursors.delete(message.client_id);
				} else {
					cursors.set(message.client_id, {
						clientId: message.client_id,
						from: message.from,
						to: message.to,
						user: message.user
					});
				}

				editor.cursors.set([...cursors.values()]);
			},
			catchUp
		);
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
		cursors: {
			onLocalCursorChange: handleLocalCursorChange
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
