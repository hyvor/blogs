<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { postOriginalStore, postStore, postVariantStore } from '../../../../postStore';
	import UpdateModal from './UpdateModal.svelte';
	import { hasPublishedChanges } from './published-changes';
	import { getI18n } from '../../../../../../../lib/i18n';

	const i18n = getI18n();

	let hasChanges = $state(false);
	let isUpdating = $state(false);

	$effect(() => {
		$postStore;
		$postOriginalStore;

		hasChanges = hasPublishedChanges();
	});
</script>

{#if $postVariantStore.status === 'published'}
	<Button disabled={!hasChanges} on:click={() => (isUpdating = true)} size="small"
		>{i18n.t('console.postEditor.update.button')}</Button
	>
{/if}

{#if isUpdating}
	<UpdateModal bind:show={isUpdating} />
{/if}
