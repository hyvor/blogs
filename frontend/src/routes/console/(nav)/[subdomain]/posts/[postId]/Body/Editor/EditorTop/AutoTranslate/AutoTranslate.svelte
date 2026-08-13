<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import {
		postCurrentContentKey,
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

	let loading = $state(false);

	function handleTranslate() {
		const primaryLanguageId = getPrimaryLanguage().id;
		const variantId = $postStore.variant_statuses.find(
			(v) => v.language_id === primaryLanguageId
		)?.id;

		if (!variantId) {
			toast.error('Primary language variant not found');
			return;
		}

		loading = true;

		autoTranslate(variantId, $postVariantLanguageStore!.code)
			.then((res) => {
				const updates = {
					title: res.title,
					description: res.description,
					[$postCurrentContentKey]: res.content
				} as Partial<PostVariant>;

				if ($postVariantStore?.slug === null) {
					updates.slug = res.slug;
				}

				updatePostVariantStore(updates);
				$postEditor.setContent(res.content);
			})
			.catch((err) => {
				toast.error('Auto-translation failed. Please try again later: ' + err.message);
			})
			.finally(() => {
				loading = false;
			});
	}
</script>

{#if $postVariantLanguageStore && $postVariantLanguageStore.is_primary === false}
	<Button size="small" style="margin-inline-end:8px" color="input" on:click={handleTranslate}>
		Auto-Translate
		{#snippet end()}
			{#if loading}
				<Loader size="small" colorTrack="transparent" />
			{:else}
				<IconMagic />
			{/if}
		{/snippet}
	</Button>
{/if}
