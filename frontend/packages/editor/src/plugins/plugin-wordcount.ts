import { Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { getTextFromDoc } from '../helpers';
// import { getWordsCount } from '../../../routes/console/lib/seo/words';
// import { get } from 'svelte/store';
// import { postLanguageStore } from '../../../routes/console/(nav)/[subdomain]/posts/postStore';

export default function wordCountPlugin() {
	return new Plugin({
		view(editorView) {
			return new WordCountPlugin(editorView);
		}
	});
}

class WordCountPlugin {
	constructor(private view: EditorView) {
		this.updateCount(view);
	}

	update(view: EditorView) {
		this.updateCount(view);
	}

	updateCount(view: EditorView) {
		// TODO: remove global state,


		/* const wordCount = document.getElementById('pm-word-count');

		if (!wordCount) return;

		setTimeout(() => {
			const languageCode = get(postLanguageStore).code;
			const text = getTextFromDoc(view.state.doc);
			wordCount.innerHTML = getWordsCount(text, languageCode) + ' Words';
		}, 0); */ // to prevent blocking the UI
	}
}
