<script lang="ts">
	import { Button, SplitControl, confirm, toast } from '@hyvor/design/components';
	import { postVariantLanguageStore, postStore } from '../../../postStore';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { deletePost, deletePostVariant } from '../../../postActions';
	import { getPrimaryLanguage } from '../../../../../../lib/stores/languagesStore';
	import { goto } from '$app/navigation';
	import { consoleUrlWithBlog } from '../../../../../../lib/consoleUrl';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	async function handleClick() {
		const confirmed = await confirm({
			title: $postVariantLanguageStore.is_primary
				? i18n.t('console.postEditor.settings.deletePostTitle')
				: i18n.t('console.postEditor.settings.deleteVariantTitle'),
			content: $postVariantLanguageStore.is_primary
				? i18n.t('console.postEditor.settings.deletePostConfirm')
				: i18n.t('console.postEditor.settings.deleteVariantConfirm', {
						language: $postVariantLanguageStore.name
					}),
			confirmText: i18n.t('console.postEditor.settings.deleteConfirmButton'),
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
				toast.error(i18n.t('console.postEditor.settings.deleteFailed'));
			});
	}
</script>

<SplitControl>
	{#snippet label()}
		<span>{i18n.t('console.postEditor.settings.delete')}</span>
	{/snippet}
	<Button color="red" size="small" on:click={handleClick}>
		{#if $postVariantLanguageStore.is_primary}
			{i18n.t('console.postEditor.settings.deletePost')}
		{:else}
			{i18n.t('console.postEditor.settings.deleteVariant', {
				language: $postVariantLanguageStore.name
			})}
		{/if}
		{#snippet start()}
			<IconTrash />
		{/snippet}
	</Button>
</SplitControl>
