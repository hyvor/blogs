<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import {
		increaseEditorVersion,
		postEditingStatusStore,
		postStore,
		postVariantLanguageStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../../../postStore';
	import IconMagic from '@hyvor/icons/IconMagic';
	import {
		getLanguageByCode,
		languagesStore
	} from '../../../../../../../../lib/stores/languagesStore';
	import { autoTranslate } from './autoTranslateActions';
	import type { PostVariant } from '../../../../../../../../lib/types';

	let loading = $state(false);

	function handleTranslate() {
		loading = true;

		// TODO: handle postID vs postVariantID
		autoTranslate($postStore.id, $postVariantLanguageStore!.code)
			.then((res) => {
				const updates = {
					title: res.title,
					description: res.description,
					content: res.content // TODO: handle content_unsaved
				} as Partial<PostVariant>;

				if ($postVariantStore?.slug === null) {
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
