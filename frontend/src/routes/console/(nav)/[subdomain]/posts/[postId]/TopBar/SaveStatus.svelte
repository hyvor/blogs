<script lang="ts">
	import { onDestroy, onMount } from 'svelte';
	import {
		postCurrentContentKey,
		postVariantOriginalStore,
		postVariantStore
	} from '../../postStore';
	import { updatePostVariant } from '../../postActions';
	import { Tag, toast } from '@hyvor/design/components';
	import { beforeNavigate } from '$app/navigation';
	import UnsavedTag from '../Sidebar/Settings/UnsavedTag.svelte';

	let key = $derived($postCurrentContentKey as 'content' | 'content_unsaved');
	let hasChanged = $derived($postVariantStore[key] !== $postVariantOriginalStore[key]);

	let isSaving = $state(false);

	function save() {
		if (!hasChanged) return;

		isSaving = true;

		updatePostVariant({
			[key]: $postVariantStore[key]
		})
			.then(() => {
				isSaving = false;
			})
			.catch((e) => {
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
		if (hasChanged) {
			if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
				navigation.cancel();
			}
		}
	});
</script>

<svelte:window onkeydown={handleKeydown} />

<span class="save-text">
	<UnsavedTag
		show={hasChanged}
		loaderState={isSaving ? 'loading' : 'none'}
		size="small"
		addMarginTop={false}
	/>

	{#if !hasChanged}
		<Tag size="small" color="green">Saved</Tag>
	{/if}
</span>

<style>
	.save-text {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
