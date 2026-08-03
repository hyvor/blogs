<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import {
		increaseEditorVersion,
		postCurrentContentKey,
		postEditingStatusStore,
		postLanguageStore,
		postStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../../../postStore';
	import IconMagic from '@hyvor/icons/IconMagic';
	import { languagesStore } from '../../../../../../../../lib/stores/languagesStore';
	import { autoTranslate } from './autoTranslateActions';
	import type { PostVariant } from '../../../../../../../../lib/types';

	function handleTranslate() {
		const variant = $postStore.variants.find(
			(v) => v.language_id === $languagesStore.find((l) => l.is_primary === true)!.id
		)!;

		autoTranslate(variant.id, $postLanguageStore.code)
			.then((res) => {
				const updates = {
					//title: res.title,
					// description: res.description,
					[$postCurrentContentKey]: res.content
				} as Partial<PostVariant>;

				// if ($postVariantStore.slug === null) {
				// 	updates.slug = res.slug;
				// }

				updatePostVariantStore(updates);
				increaseEditorVersion();

				// toast.success('Successfully translated');

				// show = false;
			})
			.finally(() => {
				//
			});
	}
</script>

{#if !$postLanguageStore.is_primary}
	<Button
		size="small"
		style="margin-inline-end:8px"
		color="input"
		on:click={handleTranslate}
		disabled={$postVariantStore.status === 'published' &&
			!$postEditingStatusStore.isEditingPublished}
	>
		Auto-Translate {#snippet end()}
			<IconMagic />
		{/snippet}
	</Button>
{/if}

<!-- {#if show}
	<TranslateModal bind:show />
{/if} -->
