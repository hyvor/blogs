<script lang="ts">
	import { confirm } from '@hyvor/design/components';
	import {
		documentStore,
		postEditingPublished,
		postEditor,
		postVariantStore
	} from '../../../../../postStore';
	import { getDiffWordsCount } from '$lib/components/Diff/diff';
	import { getTextFromContent } from '../../../../../../../../lib/prosemirror/helpers';
	import ConfirmModal from './ConfirmModal.svelte';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();

	async function handleClick() {
		const wordsCount = getDiffWordsCount(
			getTextFromContent($postVariantStore.content),
			getTextFromContent($documentStore.checkpoint_content)
		);

		if (
			await confirm({
				title: i18n.t('console.postEditor.publishedEditing.confirmTitle'),
				content: ConfirmModal,
				contentProps: { words: wordsCount },
				danger: true,
				confirmText: i18n.t('console.postEditor.publishedEditing.confirmButton')
			})
		) {
			$postEditor.setContent($postVariantStore.content!);
			postEditingPublished.set(false);
		}
	}
</script>

{#if $postEditingPublished}
	<span>
		{i18n.t('console.postEditor.publishedEditing.status')}
		<button onclick={handleClick}>{i18n.t('console.postEditor.publishedEditing.discard')}</button>
	</span>
{/if}

<style>
	span {
		font-size: 13px;
		color: var(--red-dark);
	}
	button {
		text-decoration: underline;
	}
</style>
