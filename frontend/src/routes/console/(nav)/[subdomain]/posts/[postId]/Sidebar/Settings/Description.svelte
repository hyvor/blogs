<script lang="ts">
	import { Loader, SplitControl, Textarea } from '@hyvor/design/components';
	import {
		postVariantOriginalStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import UnsavedTag from './UnsavedTag.svelte';
	import { updatePostVariant } from '../../../postActions';

	function handleInput(e: any) {
		updatePostVariantStore({ description: e.target.value });
	}

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function handleBlur(e: any) {
		const desc = (e.target.value as string).trim();

		if (desc === $postVariantOriginalStore.description) return;

		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePostVariant({ description: desc })
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
			Description
			<UnsavedTag
				show={$postVariantStore.description !== $postVariantOriginalStore.description}
				{loaderState}
			/>
		</span>
	{/snippet}
	<Textarea
		block
		rows={4}
		value={$postVariantStore.description || ''}
		on:input={handleInput}
		on:blur={handleBlur}
		maxlength={255}
	/>
</SplitControl>
