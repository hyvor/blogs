<script lang="ts">
	import { Button, SplitControl, confirm, toast } from '@hyvor/design/components';
	import {
		postVariantLanguageStore,
		postStore,
		removePostVariantStore,
		updatePostEditingStatusValue
	} from '../../../postStore';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deletePost, deletePostVariant } from '../../../postActions';
	import { getPrimaryLanguage } from '../../../../../../lib/stores/languagesStore';
	import { goto } from '$app/navigation';
	import { blogStore } from '../../../../../../lib/stores/blogStore';
	import { consoleUrlWithBlog } from '../../../../../../lib/consoleUrl';

	async function handleClick() {
		if (
			await confirm({
				title: $postVariantLanguageStore.is_primary ? 'Delete Post' : 'Delete Variant',
				content:
					($postVariantLanguageStore.is_primary
						? 'Are you sure you want to delete this post?'
						: 'Are you sure you want to delete the ' +
							$postVariantLanguageStore.name +
							' variant?') + ' This action is IRREVERSIBLE.',
				confirmText: 'Yes, Delete',
				danger: true
			})
		) {
			const toastId = toast.loading('Deleting...');

			const func = $postVariantLanguageStore.is_primary ? deletePost : deletePostVariant;

			func()
				.then(() => {
					toast.success(
						'Deleted' + ($postVariantLanguageStore.is_primary ? ' post' : ' variant'),
						{
							id: toastId
						}
					);

					if ($postVariantLanguageStore.is_primary) {
						goto(consoleUrlWithBlog($postStore.is_page ? '/pages' : '/posts'));
					} else {
						const languageId = $postVariantLanguageStore.id;
						updatePostEditingStatusValue('languageId', getPrimaryLanguage().id);
						removePostVariantStore(languageId);
					}
				})
				.catch(() => {
					toast.error('Failed to delete', { id: toastId });
				});
		}
	}
</script>

<SplitControl>
	{#snippet label()}
		<span> Delete </span>
	{/snippet}
	<Button color="red" size="small" on:click={handleClick}>
		{#if $postVariantLanguageStore.is_primary}
			Delete Post
		{:else}
			Delete {$postVariantLanguageStore.name} Variant
		{/if}
		{#snippet start()}
			<IconTrash />
		{/snippet}
	</Button>
</SplitControl>
