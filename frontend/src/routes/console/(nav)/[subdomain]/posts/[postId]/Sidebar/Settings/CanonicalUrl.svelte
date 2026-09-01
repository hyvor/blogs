<script lang="ts">
	import { SplitControl, TextInput } from '@hyvor/design/components';
	import UnsavedTag from './UnsavedTag.svelte';
	import {
		postOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../postStore';
	import { updatePost } from '../../../postActions';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function handleInput(e: any) {
		updatePostStore({ canonical_url: e.target.value });
	}

	function handleBlur(e: any) {
		const canonicalUrl = (e.target.value as string).trim();

		if (canonicalUrl === $postOriginalStore.canonical_url) return;

		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePost({ canonical_url: canonicalUrl })
				.then(() => {
					loaderState = 'success';
				})
				.catch((err) => {
					loaderState = 'error';
				});
		}
	}
</script>

<SplitControl>
	{#snippet label()}
		<span>
			{i18n.t('console.postEditor.settings.canonicalUrl')}

			<UnsavedTag
				show={($postStore.canonical_url || '') !== ($postOriginalStore.canonical_url || '')}
				{loaderState}
			/>
		</span>
	{/snippet}
	<TextInput block value={$postStore.canonical_url} on:input={handleInput} on:blur={handleBlur} />
</SplitControl>
