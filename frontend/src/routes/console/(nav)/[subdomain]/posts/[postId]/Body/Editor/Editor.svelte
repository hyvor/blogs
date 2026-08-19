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
	import { subscribeToCollabTopic, collabTopic } from './collab';
	import { authUserStore } from '../../../../../../lib/stores';

	// identifies this browser tab's editor session to the collab authority/other clients -
	// see editorConfig.collab and PostVariantCollabController on the backend
	const clientId = Math.random().toString(36).slice(2);

	let uniqueKey = $derived(String($postVariantStore.id));

	function handleChange() {
		$postContentDirtyStore = true;
	}

	function handleEvent(name: keyof HTMLElementEventMap, event: Event) {
		// handleEditorEventHandlers(name, event);
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
	function applyConfirmedSteps(steps: CollabStepJSON[], clientIds: CollabClientID[], resultingVersion: number) {
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

			// stale - the response carries exactly the steps we're missing (see
			// PostVariantCollabService::submitSteps), straight from post_variant_steps rather
			// than waiting on Mercure to somehow redeliver them. Applying them rebases our
			// still-pending steps, and prosemirror-collab automatically re-fires onSendable
			// with the rebased batch - no manual retry needed here.
			if (!response.accepted) {
				applyConfirmedSteps(
					response.steps as CollabStepJSON[],
					response.client_ids as CollabClientID[],
					response.version
				);
			}
		} catch (e) {
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

	$effect(() => {
		const editor = $postEditor;
		if (!editor) return;

		if (backlogSteps.length > 0) {
			applyConfirmedSteps(backlogSteps, backlogClientIds, liveVersion);
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
		return subscribeToCollabTopic(
			topic,
			(steps, clientIds, version) => {
				applyConfirmedSteps(steps, clientIds, version);
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
