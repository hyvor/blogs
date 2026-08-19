<script lang="ts">
	import { onDestroy, onMount } from 'svelte';
	import {
		postContentDirtyStore,
		postEditor,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import { checkpointPostVariant } from '../../../postActions';
	import { toast } from '@hyvor/design/components';
	import { beforeNavigate } from '$app/navigation';

	let hasChanged = $derived($postContentDirtyStore);

	let isSaving = $state(false);

	function save() {
		const editor = $postEditor;
		if (!hasChanged || !editor) return;

		isSaving = true;

		const content = JSON.stringify(editor.getContent());
		const version = editor.collab.getVersion();

		checkpointPostVariant({ post_variant_id: $postVariantStore.id, version, content })
			.then(() => {
				updatePostVariantStore({ content_unsaved: content, document_version: version }, true);
				$postContentDirtyStore = false;
				isSaving = false;
			})
			.catch((e) => {
				isSaving = false;
				// stale version - document_version has moved on since this editor last caught up
				// via Mercure; harmless, the next interval retries once it has
				if (e.code !== 409) {
					toast.error(`Failed to save post content: ${e.message}`);
				}
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
