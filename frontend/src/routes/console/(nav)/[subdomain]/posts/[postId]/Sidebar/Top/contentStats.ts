import { get } from 'svelte/store';
import { documentStore, postVariantLanguageStore, postVariantStore } from '../../../postStore';
import { getTextFromContent } from '../../../../../../lib/prosemirror/helpers';
import { getWordsCount } from '../../../../../../lib/seo/words';

const WORDS_PER_MINUTE = 200;

export interface ContentStats {
	wordCount: number;
	readingMinutes: number;
}

export const emptyContentStats: ContentStats = {
	wordCount: 0,
	readingMinutes: 0
};

export function getContentStats(): ContentStats {
	const document = get(documentStore);
	const language = get(postVariantLanguageStore);

	const text = getTextFromContent(document.checkpoint_content ?? null);
	const wordCount = getWordsCount(text, language?.code ?? '');
	const readingMinutes = wordCount > 0 ? Math.max(1, Math.round(wordCount / WORDS_PER_MINUTE)) : 0;

	return { wordCount, readingMinutes };
}
