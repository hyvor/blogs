<script lang="ts">
	import {
		postEditingPublished,
		postEditor,
		postContentDirtyStore,
		postSuggestionModeStore,
		postVariantStore,
		documentStore
	} from '../../../postStore';
	import { Editor, type Author, type CollabSendable, type RemoteCursor } from '@hyvor/richtext';
	import wordCountPlugin from './plugins/plugin-wordcount';
	import focusTitlePlugin from './plugins/plugin-focus-title';
	import scrollMarginPlugin from './plugins/plugin-scroll-margin';
	import { editorConfig, schema } from './editor';
	import { resolveAuthor, suggestionSource } from './suggestions';
	import { submitCollabSteps, submitCollabCursor, syncCollabSteps } from '../../../postActions';
	import { subscribeToCollabMercureTopic, collabTopic, applyConfirmedSteps } from './collab';
	import { onDestroy } from 'svelte';
	import { authUserStore } from '../../../../../../lib/stores';

	// unique client ID for this tab
	const clientId = Math.random().toString(36).slice(2);

	function handleChange() {
		$postContentDirtyStore = true;
	}

	// checkSendable (in @hyvor/richtext) fires onSendable synchronously on every keystroke, with
	// no debounce or in-flight tracking of its own - during fast typing this would otherwise fire
	// several overlapping submitCollabSteps requests, all based on the same not-yet-confirmed
	// version. Only the first one the server processes can be accepted; the rest are redundant
	// rejections. Queueing here ensures only one submission is ever in flight. A newer sendable
	// batch always contains everything an older, not-yet-sent one had, so a fresher pending batch
	// simply replaces whatever was queued but not yet sent rather than both being sent in turn.
	let pendingSendable: CollabSendable | null = null;
	let sendingSteps = false;

	function handleSendable(sendable: CollabSendable) {
		pendingSendable = sendable;
		processSendQueue();
	}

	async function processSendQueue() {
		if (sendingSteps) return;
		sendingSteps = true;
		try {
			while (pendingSendable) {
				const sendable = pendingSendable;
				pendingSendable = null;
				await submitSendable(sendable);
			}
		} finally {
			sendingSteps = false;
		}
	}

	async function submitSendable(sendable: CollabSendable) {
		try {
			const response = await submitCollabSteps({
				post_variant_id: $postVariantStore.id,
				version: sendable.version,
				steps: sendable.steps,
				client_id: String(sendable.clientID)
			});
			applyConfirmedSteps($postEditor!, response.steps);
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
		$documentStore.checkpoint_content ||
			JSON.stringify({ type: 'doc', content: [{ type: 'paragraph', content: [] }] })
	);

	// document_version is the live collab version; the steps returned alongside it are exactly
	// the ones not yet reflected in `value` (see PostVariantCollabService on the backend) - so
	// the editor must start at the version *before* those steps, then fast-forward via
	// collab.receiveSteps() once mounted.
	let backlogSteps = $derived($documentStore.pending_steps.steps);

	let isEditable = $derived($postVariantStore.status === 'draft' || $postEditingPublished);

	$effect(() => {
		$postEditor.setEditable(isEditable);
	});

	let topicUnsubscriber: () => void;

	function handleInit() {
		const editor = $postEditor!;

		if (backlogSteps.length > 0) {
			applyConfirmedSteps(editor, backlogSteps);
		}

		const cursors = new Map<string, RemoteCursor>();

		const topic = collabTopic($postVariantStore.id);

		topicUnsubscriber = subscribeToCollabMercureTopic(
			topic,
			$documentStore.mercure_token,
			(steps) => {
				applyConfirmedSteps(editor, steps);
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
			}
		);
	}

	let fullEditorConfig = $derived({
		...editorConfig,
		collab: {
			version: $documentStore.checkpoint_version,
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
		},
		colorButtonBackground: 'var(--accent)'
	});

	onDestroy(() => {
		topicUnsubscriber?.();
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
			plugins={[wordCountPlugin(), focusTitlePlugin(), scrollMarginPlugin()]}
			oninit={handleInit}
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
