<script lang="ts">
	import { Button, Modal, Tooltip, toast } from '@hyvor/design/components';
	import { postStore, postVariantStore } from '../../../postStore';
	import { unpublishPostVariant } from '../../../postActions';
	import IconEyeSlash from '@hyvor/icons/IconEyeSlash';
	import { getI18n } from '../../../../../../lib/i18n';
	import { authUserStore } from '../../../../../../lib/stores';
	import { can } from '../../../../../../lib/scope.svelte';

	const i18n = getI18n();

	let modalOpen = $state(false);

	let isAuthor = $derived(
		($postStore?.authors ?? []).some((author) => author.hyvor_user_id === $authUserStore?.id)
	);

	let canPublish = $derived(can('posts.publish.all') || (isAuthor && can('posts.publish.own')));

	const publishPermissionTooltip = i18n.t('console.postEditor.publish.noPermission');

	function handleUnpublish() {
		modalOpen = false;

		const toastId = toast.loading(i18n.t('console.postEditor.unpublish.unpublishing'));

		unpublishPostVariant()
			.then(() => {
				toast.success(i18n.t('console.postEditor.unpublish.unpublished'), { id: toastId });
			})
			.catch((e) => {
				toast.error(e?.message || i18n.t('console.postEditor.unpublish.unpublishFailed'), {
					id: toastId
				});
			});
	}
</script>

{#if $postVariantStore.status !== 'draft'}
	<Tooltip text={!canPublish ? publishPermissionTooltip : ''}>
		<Button size="small" color="input" disabled={!canPublish} on:click={() => (modalOpen = true)}>
			{#snippet start()}
				<IconEyeSlash size={12} />
			{/snippet}
			{#if $postVariantStore.status === 'scheduled'}
				{i18n.t('console.postEditor.unpublish.unschedule')}
			{:else}
				{i18n.t('console.postEditor.unpublish.unpublish')}
			{/if}
		</Button>
	</Tooltip>

	<Modal
		title={$postVariantStore.status === 'scheduled'
			? i18n.t('console.postEditor.unpublish.unscheduleTitle')
			: i18n.t('console.postEditor.unpublish.unpublishTitle')}
		bind:show={modalOpen}
		size="small"
	>
		{$postVariantStore.status === 'scheduled'
			? i18n.t('console.postEditor.unpublish.confirmUnschedule')
			: i18n.t('console.postEditor.unpublish.confirmUnpublish')}

		{#snippet footer()}
			<div>
				<Button variant="invisible" on:click={() => (modalOpen = false)}
					>{i18n.t('console.common.cancel')}</Button
				>
				<Button color="red" on:click={handleUnpublish}>
					{$postVariantStore.status === 'scheduled'
						? i18n.t('console.postEditor.unpublish.unschedule')
						: i18n.t('console.postEditor.unpublish.unpublish')}
				</Button>
			</div>
		{/snippet}
	</Modal>
{/if}
