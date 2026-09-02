<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import {
		documentStore,
		postOriginalStore,
		postStore,
		postVariantOriginalStore,
		postVariantStore
	} from '../../../../postStore';
	import UpdateModal from './UpdateModal.svelte';
	import { hasPublishedChanges } from './published-changes';
	import { getI18n } from '../../../../../../../lib/i18n';

	const i18n = getI18n();

	let hasChanges = $state(false);
	let isUpdating = $state(false);

	$effect(() => {
		void $postStore;
		void $postOriginalStore;
		void $postVariantStore;
		void $postVariantOriginalStore;
		void $documentStore;

		hasChanges = hasPublishedChanges();
	});
</script>

{#if $postVariantStore.status !== 'draft'}
	<Button disabled={!hasChanges} on:click={() => (isUpdating = true)} size="small"
		>{i18n.t('console.postEditor.update.button')}</Button
	>
{/if}

{#if isUpdating}
	<UpdateModal bind:show={isUpdating} />
{/if}
