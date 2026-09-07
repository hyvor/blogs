<script lang="ts">
	import { Button, Tooltip } from '@hyvor/design/components';
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
	import { authUserStore } from '../../../../../../../lib/stores';
	import { can } from '../../../../../../../lib/scope.svelte';

	const i18n = getI18n();

	let hasChanges = $state(false);
	let isUpdating = $state(false);

	let isAuthor = $derived(
		($postStore?.authors ?? []).some((author) => author.hyvor_user_id === $authUserStore?.id)
	);

	let canPublish = $derived(can('posts.publish.all') || (isAuthor && can('posts.publish.own')));

	const publishPermissionTooltip = i18n.t('console.postEditor.publish.noPermission');

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
	<Tooltip text={!canPublish ? publishPermissionTooltip : ''}>
		<Button disabled={!hasChanges || !canPublish} on:click={() => (isUpdating = true)} size="small"
			>{i18n.t('console.postEditor.update.button')}</Button
		>
	</Tooltip>
{/if}

{#if isUpdating}
	<UpdateModal bind:show={isUpdating} />
{/if}
