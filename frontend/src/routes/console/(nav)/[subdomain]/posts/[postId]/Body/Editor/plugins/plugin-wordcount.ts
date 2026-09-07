import { Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { getTextFromDoc } from '../../../../../../../lib/prosemirror/helpers';
import { getWordsCount } from '../../../../../../../lib/seo/words';
import { get } from 'svelte/store';
import { postVariantLanguageStore } from '../../../../postStore';

export default function wordCountPlugin(format: (count: number) => string) {
	return new Plugin({
		view(editorView) {
			return new WordCountPlugin(editorView, format);
		}
	});
}

class WordCountPlugin {
	constructor(
		private view: EditorView,
		private format: (count: number) => string
	) {
		this.updateCount(view);
	}

	update(view: EditorView) {
		this.updateCount(view);
	}

	updateCount(view: EditorView) {
		const wordCount = document.getElementById('pm-word-count');

		if (!wordCount) return;

		setTimeout(() => {
			const languageCode = get(postVariantLanguageStore).code;
			const text = getTextFromDoc(view.state.doc);
			wordCount.innerHTML = this.format(getWordsCount(text, languageCode));
		}, 0); // to prevent blocking the UI
	}
}
