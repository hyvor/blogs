<script lang="ts">
	import { Button, Modal, toast } from '@hyvor/design/components';
	import { postVariantStore } from '../../../postStore';
	import { unpublishPostVariant } from '../../../postActions';
	import IconEyeSlash from '@hyvor/icons/IconEyeSlash';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let modalOpen = $state(false);

	function handleUnpublish() {
		modalOpen = false;

		const toastId = toast.loading(i18n.t('console.postEditor.unpublish.unpublishing'));

		unpublishPostVariant()
			.then(() => {
				toast.success(i18n.t('console.postEditor.unpublish.unpublished'), { id: toastId });
			})
			.catch(() => {
				toast.error(i18n.t('console.postEditor.unpublish.unpublishFailed'), { id: toastId });
			});
	}
</script>

{#if $postVariantStore.status !== 'draft'}
	<Button size="small" color="input" on:click={() => (modalOpen = true)}>
		{#snippet start()}
			<IconEyeSlash size={12} />
		{/snippet}
		{#if $postVariantStore.status === 'scheduled'}
			{i18n.t('console.postEditor.unpublish.unschedule')}
		{:else}
			{i18n.t('console.postEditor.unpublish.unpublish')}
		{/if}
	</Button>

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
