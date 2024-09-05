import {EditorView, type NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode} from 'prosemirror-model';

export default class HeadingNodeView implements NodeView {

    dom: HTMLElement;
    contentDOM: HTMLElement;

    private inputWrap: HTMLDivElement;
    private input: HTMLInputElement;

    constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

        this.dom = document.createElement("div");
        this.dom.className = "heading-wrap";

        const headingSelectorsWrap = document.createElement('div');
		headingSelectorsWrap.classList.add('heading-selectors-wrap');
		[1, 2, 3, 4, 5, 6].map((level) => {
			const selector = document.createElement('button');
			selector.type = 'button';
			selector.classList.add('heading-selector');
			selector.textContent = 'H' + level;
			selector.addEventListener('click', () => {
				const { state, dispatch } = view;
				const { tr } = state;
				tr.setNodeMarkup(getPos()!, null, { level });
				dispatch(tr);
			});

			if (node.attrs.level === level) {
				selector.classList.add('selected');
			}

			headingSelectorsWrap.appendChild(selector);
			return selector;
		});
		this.dom.appendChild(headingSelectorsWrap);

        this.contentDOM = document.createElement("h" + node.attrs.level)
        const id = node.attrs.id || "";
        this.contentDOM.id = id
        this.dom.appendChild(this.contentDOM);

        this.inputWrap = document.createElement("div");
        this.inputWrap.contentEditable = "false";
        this.dom.appendChild(this.inputWrap)

        // id input
        this.input = document.createElement("div");
        this.input.value = id;

        this.input.oninput = function(e) {
            const pos = getPos();

            if (pos === undefined)
                return;

            view.dispatch(
                view.state.tr.setNodeMarkup(
                    pos,
                    null,
                    {...node.attrs, id: (e.target as HTMLInputElement).value }
                )
            )
        }

        this.inputWrap.appendChild(this.input);
    }

    update(node: ProsemirrorNode) {
        if (node.type.name === 'heading') {
            this.contentDOM.id = node.attrs.id;
            this.input.value = node.attrs.id;
            return true;
        }

        return false;
    }

    stopEvent(e: Event) {
        return (e.target as Node).isEqualNode(this.input)
    }


}