<script lang="ts">
	import { onDestroy, onMount } from 'svelte';
	import {
		postContentDirtyStore,
		postEditor,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import {
		saveCheckpoint,
		type CheckpointClientAheadError,
		type CheckpointClientBehindError
	} from '../../documentActions';
	import { toast } from '@hyvor/design/components';
	import { beforeNavigate } from '$app/navigation';
	import type { CollabClientID, CollabStepJSON } from '@hyvor/richtext';
	import { applyConfirmedSteps } from '../Editor/collab';

	let hasChanged = $derived($postContentDirtyStore);

	let isSaving = $state(false);

	function save() {
		const editor = $postEditor;
		if (!hasChanged || !editor) return;

		isSaving = true;

		const content = JSON.stringify(editor.getContent());
		const version = editor.collab.getVersion();

		saveCheckpoint({ post_variant_id: $postVariantStore.id, version, content })
			.then(() => {
				updatePostVariantStore({ content_unsaved: content, document_version: version }, true);
				$postContentDirtyStore = false;
				isSaving = false;
			})
			.catch((e) => {
				isSaving = false;

				if (e.code === 409 && e.body?.message === 'client_behind') {
					// server moved on since this editor last caught up via Mercure - apply the
					// steps we're missing so the next save attempt has a version the server
					// recognizes, same as a rejected submitCollabSteps (see Editor.svelte)
					const body = e.body as CheckpointClientBehindError;
					if (editor && body.steps.length > 0) {
						applyConfirmedSteps(editor, body.steps as CollabStepJSON[]);
					}
					return;
				}

				if (e.code === 409 && e.body?.message === 'client_ahead') {
					// should never happen in normal operation - surface it instead of retrying
					const body = e.body as CheckpointClientAheadError;
					toast.error('Failed to save: your editor is out of sync. Please reload the page.');
					console.error('checkpoint client_ahead', body.message_full);
					return;
				}

				toast.error(`Failed to save post content: ${e.message}`);
			});
	}

	function handleKeydown(e: KeyboardEvent) {
		if ((e.ctrlKey || e.metaKey) && e.key === 's') {
			e.preventDefault();
			save();
		}
	}

	let autoSaveInterval: ReturnType<typeof setInterval>;

	onMount(() => {
		autoSaveInterval = setInterval(save, 15000);
	});

	onDestroy(() => {
		clearInterval(autoSaveInterval);
	});

	beforeNavigate((navigation) => {
		return;
		if (hasChanged) {
			if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
				navigation.cancel();
			}
		}
	});
</script>

<svelte:window onkeydown={handleKeydown} />

<span class="save-text">
	{#if isSaving}
		Saving...
	{:else if hasChanged}
		Unsaved changes
	{:else}
		Saved
	{/if}
</span>

<style>
	.save-text {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
