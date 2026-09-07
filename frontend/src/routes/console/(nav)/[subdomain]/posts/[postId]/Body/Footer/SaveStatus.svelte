<script lang="ts">
	import { onDestroy, onMount } from 'svelte';
	import {
		postContentDirtyStore,
		postEditor,
		postVariantStore,
		updateDocumentStore
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
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

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
				updateDocumentStore({ checkpoint_content: content, checkpoint_version: version });
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
					toast.error(i18n.t('console.postEditor.save.outOfSync'));
					console.error('checkpoint client_ahead', body.message_full);
					return;
				}

				toast.error(i18n.t('console.postEditor.save.failed', { message: e.message }));
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
		if (hasChanged) {
			if (!confirm(i18n.t('console.postEditor.save.leaveConfirm'))) {
				navigation.cancel();
			}
		}
	});
</script>

<svelte:window onkeydown={handleKeydown} />

<span class="save-text">
	{#if isSaving}
		{i18n.t('console.postEditor.save.saving')}
	{:else if hasChanged}
		{i18n.t('console.postEditor.save.unsaved')}
	{:else}
		{i18n.t('console.postEditor.save.saved')}
	{/if}
</span>

<style>
	.save-text {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
