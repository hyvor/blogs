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
		updatePostStore({ code_head: value });
	}

	function handleConfirm() {
		if ($postVariantStore.status !== 'published') {
			loaderState = 'loading';

			updatePost({
				code_head: $postStore.code_head
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
				label={i18n.t('console.postEditor.settings.codeHead')}
				info={i18n.t('console.postEditor.settings.codeHeadInfo')}
			/>
			<UnsavedTag show={$postStore.code_head !== $postOriginalStore.code_head} {loaderState} />
		</span>
	{/snippet}

	<CodemirrorWithPreview
		value={$postStore.code_head || ''}
		title={i18n.t('console.postEditor.settings.codeHead')}
		onchange={handleChange}
		onconfirm={handleConfirm}
	/>
</SplitControl>
