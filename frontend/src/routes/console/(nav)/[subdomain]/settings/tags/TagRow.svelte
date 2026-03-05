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
				title: 'Delete tag',
				content: 'Are you sure you want to delete this tag?',
				confirmText: 'Yes, delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting tag...');

			deleteTag(tag.id)
				.then(() => {
					toast.success('Tag deleted.', { id: toastId });
					dispatch('delete', tag.id);
				})
				.catch((e) => {
					toast.error(e.message, { id: toastId });
				});
		}
	}
</script>

<TableRow>
	<div><TagName {tag} /></div>
	<div>
		{#if tag.is_private}
			{tag.slug}
		{:else}
			<Link href={variant?.url || ''} target="_blank">
				{tag.slug}
			</Link>
		{/if}
	</div>
	<div>{variant?.description || ''}</div>
	<div>{tag.posts_count}</div>
	<div>
		<Tooltip text="Edit tag">
			<IconButton
				variant="fill-light"
				color="gray"
				size="small"
				on:click={() => (isEditing = true)}
			>
				<IconPencilFill size={12} />
			</IconButton>
		</Tooltip>
		<Tooltip text="Delete tag">
			<IconButton variant="fill-light" color="red" size="small" on:click={handleDelete}>
				<IconTrash size={12} />
			</IconButton>
		</Tooltip>
	</div>
</TableRow>

{#if isEditing}
	<UpdateTagModal bind:show={isEditing} {tag} on:variantCreate on:update />
{/if}
