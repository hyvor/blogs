<script lang="ts">
	import { SplitControl } from '@hyvor/design/components';
	import UnsavedTag from './UnsavedTag.svelte';
	import {
		postOriginalStore,
		postStore,
		postVariantStore,
		updatePostStore
	} from '../../../postStore';
	import LabelWithInfo from './LabelWithInfo.svelte';
	import CodemirrorWithPreview from '../../../../../../lib/components/CodemirrorEditor/CodemirrorWithPreview.svelte';
	import { updatePost } from '../../../postActions';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function handleChange(value: string) {
		updatePostStore({ code_foot: value });
	}

	function handleConfirm() {
		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePost({
				code_foot: $postStore.code_foot
			})
				.then(() => {
					loaderState = 'success';
				})
				.catch(() => {
					loaderState = 'error';
				});
		}
	}
</script>

<SplitControl>
	{#snippet label()}
		<span>
			<LabelWithInfo
				label={i18n.t('console.postEditor.settings.codeFoot')}
				info={i18n.t('console.postEditor.settings.codeFootInfo')}
			/>
			<UnsavedTag show={$postStore.code_foot !== $postOriginalStore.code_foot} {loaderState} />
		</span>
	{/snippet}

	<CodemirrorWithPreview
		value={$postStore.code_foot || ''}
		title={i18n.t('console.postEditor.settings.codeFoot')}
		onchange={handleChange}
		onconfirm={handleConfirm}
	/>
</SplitControl>
