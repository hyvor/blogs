<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
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

	let loading = $state(false);

	function handleTranslate() {
		const variant = $postStore.variants.find(
			(v) => v.language_id === $languagesStore.find((l) => l.is_primary === true)!.id
		)!;

		loading = true;

		autoTranslate(variant.id, $postLanguageStore.code)
			.then((res) => {
				const updates = {
					title: res.title,
					description: res.description,
					[$postCurrentContentKey]: res.content
				} as Partial<PostVariant>;

				if ($postVariantStore.slug === null) {
					updates.slug = res.slug;
				}

				updatePostVariantStore(updates);
				increaseEditorVersion();
			})
			.catch((err) => {
				toast.error('Auto-translation failed. Please try again later: ' + err.message);
			})
			.finally(() => {
				loading = false;
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

<!-- {#if show}
	<TranslateModal bind:show />
{/if} -->
