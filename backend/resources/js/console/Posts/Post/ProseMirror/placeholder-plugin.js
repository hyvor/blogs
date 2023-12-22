
import { Plugin } from 'prosemirror-state';

export default function placeholderPlugin(text) {
    const update = (view) => {
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