import { Plugin } from 'prosemirror-state';
import type { EditorView } from "prosemirror-view";

export default function placeholderPlugin(text: string) {
    const update = (view: EditorView) => {
        if (view.state.doc.toString() !== 'doc(paragraph)') {
            view.dom.removeAttribute('data-placeholder');
        } else {
            view.dom.setAttribute('data-placeholder', text);
        }
    };

    return new Plugin({
        view(view) {
            update(view);
            return { update };
        }
    });
}