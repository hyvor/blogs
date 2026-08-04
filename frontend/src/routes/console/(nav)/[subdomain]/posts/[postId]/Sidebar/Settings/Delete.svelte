<script lang="ts">
	import { Button, SplitControl, confirm, toast } from '@hyvor/design/components';
	import { postVariantLanguageStore, postStore } from '../../../postStore';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deletePost, deletePostVariant } from '../../../postActions';
	import { getPrimaryLanguage } from '../../../../../../lib/stores/languagesStore';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../../../../lib/consoleUrl';

	async function handleClick() {
		const confirmed = await confirm({
			title: $postVariantLanguageStore.is_primary ? 'Delete Post' : 'Delete Variant',
			content:
				($postVariantLanguageStore.is_primary
					? 'Are you sure you want to delete this post?'
					: 'Are you sure you want to delete the ' +
						$postVariantLanguageStore.name +
						' variant?') + ' This action is IRREVERSIBLE.',
			confirmText: 'Yes, Delete',
			danger: true,
			autoClose: false
		});

		if (!confirmed) {
			return;
		}

		confirmed.loading();
		const func = $postVariantLanguageStore.is_primary ? deletePost : deletePostVariant;

		func()
			.then(() => {
				confirmed.close();

				if ($postVariantLanguageStore.is_primary) {
					goto(consoleUrlWithBlog($postStore.is_page ? '/pages' : '/posts'));
				} else {
					const primaryLanguageCode = getPrimaryLanguage().code;
					goto(consoleUrlWithBlog(`/posts/${$postStore.id}/${primaryLanguageCode}`));
				}
			})
			.catch(() => {
				confirmed.close();
				toast.error('Failed to delete');
			});
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
