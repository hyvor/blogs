<script lang="ts">
	import {
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
	import { subscribeToCollabMercureTopic, collabTopic } from './collab';
	import { onMount } from 'svelte';
	import { authUserStore } from '../../../../../../lib/stores';

	// unique client ID for this tab
	const clientId = Math.random().toString(36).slice(2);

	function handleChange() {
		$postContentDirtyStore = true;
	}

	// Confirmed step batches can arrive redundantly through several channels - the Mercure
	// broadcast (which echoes back to the submitting client too), a rejected submitCollabSteps's
	// catch-up payload (fetched directly instead of waiting on Mercure), and the reconnect
	// `sync` catch-up - and fast typing makes overlapping in-flight requests likely, so more
	// than one of these can end up covering the same steps. Each batch is "every step since the
	// version *that specific request* asked for", not "every step since what we've already
	// applied from a sibling response" - so a later-arriving batch can legitimately start
	// *before* our current version (it was computed before an earlier response caught us up) and
	// still end *after* it. prosemirror-collab's receiveSteps() has no idempotency of its own -
	// it just bumps its version counter by however many steps it's given, with no awareness of
	// which ones were already applied - so passing it a batch that overlaps what's already
	// applied double-counts the overlap, overshooting the local version past the server's true
	// version. Once that happens, every future submission looks stale forever with nothing left
	// to catch up on (the server has nothing newer than what it already gave us). Slicing off
	// whatever prefix of the batch is already covered by the current version - rather than just
	// checking whether the batch as a whole is newer - makes every one of these channels safely
	// idempotent no matter how they interleave.
	function applyConfirmedSteps(
		steps: CollabStepJSON[],
		clientIds: CollabClientID[],
		resultingVersion: number
	) {
		const editor = $postEditor;
		if (!editor) return;
		const currentVersion = editor.collab.getVersion();
		const alreadyApplied = currentVersion + steps.length - resultingVersion;
		if (alreadyApplied >= steps.length) return; // nothing in this batch is new
		const newSteps = alreadyApplied > 0 ? steps.slice(alreadyApplied) : steps;
		const newClientIds = alreadyApplied > 0 ? clientIds.slice(alreadyApplied) : clientIds;
		editor.collab.receiveSteps(newSteps, newClientIds);
	}

	async function handleSendable(sendable: CollabSendable) {
		try {
			const response = await submitCollabSteps({
				post_variant_id: $postVariantStore.id,
				version: sendable.version,
				steps: sendable.steps,
				client_id: String(sendable.clientID)
			});

			// if accepted, the server has already applied our steps

			// if not, we have to rebase using the steps the server sent when accepted is false.
			// prosemirror automatically refires the onSendable callback after the rebase
			// so we don't have to retry
			if (!response.accepted) {
				applyConfirmedSteps(
					response.steps as CollabStepJSON[],
					response.client_ids as CollabClientID[],
					response.version
				);
			}
		} catch (e) {
			// TODO: editor error handling
			console.error('Failed to submit collab steps', e);
		}
	}

	function handleLocalCursorChange(cursor: { from: number; to: number } | null) {
		submitCollabCursor({
			post_variant_id: $postVariantStore.id,
			client_id: clientId,
			cursor
		}).catch((e) => console.error('Failed to submit collab cursor', e));
	}

	let value = $derived(
		$postVariantStore.content_unsaved ||
			JSON.stringify({ type: 'doc', content: [{ type: 'paragraph', content: [] }] })
	);

	// document_version is the live collab version; the steps returned alongside it are exactly
	// the ones not yet reflected in `value` (see PostVariantCollabService on the backend) - so
	// the editor must start at the version *before* those steps, then fast-forward via
	// collab.receiveSteps() once mounted.
	let backlogSteps = $derived($postVariantStore.document_steps as CollabStepJSON[]);
	let backlogClientIds = $derived($postVariantStore.document_client_ids as CollabClientID[]);
	let liveVersion = $derived($postVariantStore.document_version);
	let initialVersion = $derived(liveVersion - backlogSteps.length);

	let isEditable = $derived($postVariantStore.status === 'draft' || $postEditingPublished);

	$effect(() => {
		$postEditor.setEditable(isEditable);
	});

	onMount(() => {
		const editor = $postEditor;
		if (!editor) return;

		if (backlogSteps.length > 0) {
			applyConfirmedSteps(backlogSteps, backlogClientIds, liveVersion);
		}

		const cursors = new Map<string, RemoteCursor>();

		// called when the Mercure EventSource reconnects after a drop - whatever was published
		// while we were disconnected is gone from that transport's point of view (no replay), so
		// pull the durable truth directly instead of just hoping nothing was missed
		async function catchUp() {
			try {
				const response = await syncCollabSteps({
					post_variant_id: $postVariantStore.id,
					version: editor.collab.getVersion()
				});
				if (response.steps.length > 0) {
					applyConfirmedSteps(
						response.steps as CollabStepJSON[],
						response.client_ids as CollabClientID[],
						response.version
					);
				}
			} catch (e) {
				console.error('Failed to sync collab steps', e);
			}
		}

		const topic = collabTopic($postVariantStore.id);
		return subscribeToCollabMercureTopic(
			topic,
			(steps, clientIds, version) => {
				applyConfirmedSteps(steps, clientIds, version);
			},
			(message) => {
				if (message.client_id === clientId) return; // ignore our own echo, if any

				if (
					message.clear ||
					!message.user ||
					message.from === undefined ||
					message.to === undefined
				) {
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
	<div class="wrap">
		<Editor
			bind:this={$postEditor}
			{value}
			onvaluechange={handleChange}
			editable={isEditable}
			{schema}
			editorConfig={fullEditorConfig}
			plugins={[wordCountPlugin()]}
		/>
	</div>
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
		/* padding-left: max(300px, calc((100% - 700px) / 2));
		padding-right: max(0px, calc((100% - 700px - 300px) / 2)); */
	}
</style>
