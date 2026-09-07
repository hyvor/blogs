<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import {
		postEditor,
		postStore,
		postVariantLanguageStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../../../postStore';
	import IconMagic from '@hyvor/icons/IconMagic';
	import { autoTranslate } from './autoTranslateActions';
	import type { PostVariant } from '../../../../../../../../lib/types';
	import { getPrimaryLanguage } from '../../../../../../../../lib/stores/languagesStore';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();

	let loading = $state(false);

	function handleTranslate() {
		const primaryLanguageId = getPrimaryLanguage().id;
		const variantId = $postStore.variants.find((v) => v.language_id === primaryLanguageId)?.id;

		if (!variantId) {
			toast.error(i18n.t('console.postEditor.autoTranslate.primaryVariantNotFound'));
			return;
		}

		loading = true;

		autoTranslate(variantId, $postVariantLanguageStore!.code)
			.then((res) => {
				const updates = {
					title: res.title,
					description: res.description
				} as Partial<PostVariant>;

				if ($postVariantStore?.slug === null) {
					updates.slug = res.slug;
				}

				updatePostVariantStore(updates);
				$postEditor.setContent(res.content);
			})
			.catch((err) => {
				toast.error(i18n.t('console.postEditor.autoTranslate.failed', { message: err.message }));
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

{#if $postVariantLanguageStore && $postVariantLanguageStore.is_primary === false}
	<Button size="small" style="margin-inline-end:8px" color="input" on:click={handleTranslate}>
		{i18n.t('console.postEditor.autoTranslate.button')}
		{#snippet end()}
			{#if loading}
				<Loader size="small" colorTrack="transparent" />
			{:else}
				<IconMagic />
			{/if}
		{/snippet}
	</Button>
{/if}
