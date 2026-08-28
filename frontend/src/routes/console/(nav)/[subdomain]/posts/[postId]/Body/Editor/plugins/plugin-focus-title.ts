import { Plugin, Selection } from 'prosemirror-state';
import type { EditorView } from 'prosemirror-view';
import { get } from 'svelte/store';
import { postTitle } from '../../../../postStore';

export default function focusTitlePlugin() {
	return new Plugin({
		props: {
			handleKeyDown(view: EditorView, event: KeyboardEvent) {
				if (event.key !== 'ArrowUp' && event.key !== 'ArrowLeft') {
					return false;
				}

				const { selection, doc } = view.state;
				if (!selection.empty || !Selection.atStart(doc).eq(selection)) {
					return false;
				}

				const title = get(postTitle);
				if (!title) return false;

				title.focusAtEnd();
				return true;
			}
		}
	});
}
