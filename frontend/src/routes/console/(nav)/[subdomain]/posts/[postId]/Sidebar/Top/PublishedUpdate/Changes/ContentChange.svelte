<script lang="ts">
	import { getDiffWordsCount } from '$lib/components/Diff/diff';
	import { getTextFromContent } from '../../../../../../../../lib/prosemirror/helpers';
	import { getWordsCount } from '../../../../../../../../lib/seo/words';
	import { postVariantLanguageStore } from '../../../../../postStore';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		contentOld: string | null;
		contentNew: string | null;
		diff: boolean;
	}

	let { contentOld, contentNew, diff }: Props = $props();

	let changedWords = $derived(
		getDiffWordsCount(getTextFromContent(contentOld), getTextFromContent(contentNew))
	);

	let totalWords = $derived(
		getWordsCount(getTextFromContent(contentNew), $postVariantLanguageStore.code)
	);
</script>

<span>
	{#if diff}
		<T
			key="console.postEditor.update.changedWords"
			params={{ count: changedWords, strong: { element: 'strong' } }}
		/>
	{:else}
		<T
			key="console.postEditor.update.totalWords"
			params={{ count: totalWords, strong: { element: 'strong' } }}
		/>
	{/if}
</span>
