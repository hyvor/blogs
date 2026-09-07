<script lang="ts">
	import { Checkbox, SplitControl } from '@hyvor/design/components';
	import OnlyPrimaryVariant from './OnlyPrimaryVariant.svelte';
	import {
		postOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../postStore';
	import UnsavedTag from './UnsavedTag.svelte';
	import { updatePost } from '../../../postActions';
	import LabelWithInfo from './LabelWithInfo.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function handleChange(e: any) {
		const checked = e.target.checked;

		updatePostStore({
			is_featured: checked
		});

		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePost({ is_featured: checked })
				.then(() => {
					loaderState = 'success';
				})
				.catch((err) => {
					loaderState = 'error';
				});
		}
	}
</script>

<OnlyPrimaryVariant>
	<SplitControl>
		{#snippet label()}
			<span>
				<LabelWithInfo
					label={i18n.t('console.postEditor.settings.featured')}
					info={i18n.t('console.postEditor.settings.featuredInfo')}
				/>

				<UnsavedTag
					show={$postStore.is_featured !== $postOriginalStore.is_featured}
					{loaderState}
				/>
			</span>
		{/snippet}
		<Checkbox checked={$postStore.is_featured} on:change={handleChange} />
	</SplitControl>
</OnlyPrimaryVariant>
