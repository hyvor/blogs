<script lang="ts">
	import { IconButton, Link, TableRow, Tooltip, confirm, toast } from '@hyvor/design/components';
	import type { Tag } from '../../../../lib/types';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import IconPencilFill from '@hyvor/icons/IconPencilFill';
	import IconTrash from '@hyvor/icons/IconTrash';

	import { deleteTag } from './tagActions';
	import { createEventDispatcher } from 'svelte';
	import UpdateTagModal from './Update/UpdateTagModal.svelte';
	import TagName from './TagName.svelte';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		tag: Tag;
	}

	let { tag }: Props = $props();

	let variant = $derived(tag.variants.find((v) => v.language_id === $primaryLanguageStore.id));

	let isEditing = $state(false);

	const dispatch = createEventDispatcher();

	async function handleDelete() {
		if (
			await confirm({
				title: i18n.t('console.settings.tags.deleteTag'),
				content: i18n.t('console.settings.tags.deleteContent'),
				confirmText: i18n.t('console.common.yesDelete'),
				danger: true
			})
		) {
			const toastId = toast.loading(i18n.t('console.settings.tags.deleting'));

			deleteTag(tag.id)
				.then(() => {
					toast.success(i18n.t('console.settings.tags.deleted'), { id: toastId });
					dispatch('delete', tag.id);
				})
				.catch((e) => {
					toast.error(e.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div>
		<div class="name"><TagName {tag} /></div>
		{#if variant?.description}
			<div class="description">{variant.description}</div>
		{/if}
		<div class="meta">{tag.posts_count} {tag.posts_count === 1 ? 'post' : 'posts'}</div>
	</div>
	<div>
		{#if tag.is_private}
			{tag.slug}
		{:else}
			<Link href={variant?.url || ''} target="_blank">
				{tag.slug}
			</Link>
		{/if}
	</div>
	<div>
		<Tooltip text={i18n.t('console.settings.tags.editTag')}>
			<IconButton color="input" variant="fill" size="small" on:click={() => (isEditing = true)}>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>
		<Tooltip text={i18n.t('console.settings.tags.deleteTag')}>
			<IconButton variant="fill-light" color="red" size="small" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

{#if isEditing}
	<UpdateTagModal bind:show={isEditing} {tag} on:variantCreate on:update />
{/if}

<style>
	.description {
		margin-top: 2px;
		font-size: 13px;
		color: var(--text-light);
	}

	.meta {
		margin-top: 2px;
		font-size: 12px;
		color: var(--text-light);
	}
</style>
