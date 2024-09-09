import type { Node as ProsemirrorNode } from 'prosemirror-model';
import { EditorView, type NodeView } from 'prosemirror-view';

export default class HeadingNodeView implements NodeView {

	dom: HTMLElement;
	contentDOM: HTMLElement;

	private selection: any;
	private input: HTMLInputElement;
	private inputWrap: HTMLDivElement;
    private anchor: any;

	constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
		this.dom = document.createElement('div');
		this.dom.classList.add('heading-wrap');

		// Create headingSelectorsWrap before inputWrap
		const headingSelectorsWrap = document.createElement('div');
		headingSelectorsWrap.classList.add('heading-selectors-wrap');
		[1, 2, 3, 4, 5, 6].map((level) => {
			const selector = document.createElement('button');
			selector.type = 'button';
			selector.classList.add('heading-selector');
			selector.textContent = 'H' + level;
			selector.addEventListener('mouseover', () => {
				this.selection = view.state.tr.selection;
			});
			selector.addEventListener('click', () => {
				const { state, dispatch } = view;
				const { tr } = state;

				tr.setNodeMarkup(getPos()!, null, { ...node.attrs, level });
				dispatch(tr);

				// Restore selection
				const posInNode = this.selection.from;
				let mappedPos = view.state.tr.mapping.map(posInNode);
				const newSelection = this.selection.constructor.create(view.state.tr.doc, mappedPos);
				dispatch(view.state.tr.setSelection(newSelection));
				view.focus();
			});

			if (node.attrs.level === level) {
				selector.classList.add('selected');
			}

			headingSelectorsWrap.appendChild(selector);
			return selector;
		});
		this.dom.appendChild(headingSelectorsWrap);

		// Create contentDOM
		this.contentDOM = document.createElement('h' + node.attrs.level);
		const id = node.attrs.id || "";
		this.contentDOM.id = id;
		this.dom.appendChild(this.contentDOM);

		// Create inputWrap for the input field
		this.inputWrap = document.createElement("div");
		this.inputWrap.contentEditable = "false";
		this.dom.appendChild(this.inputWrap);

		const type = document.createElement("span");
		type.innerHTML = "h" + node.attrs.level + "#";
		this.inputWrap.appendChild(type);

		// ID input
		this.input = document.createElement("input");
		this.input.value = id;

		this.input.oninput = function (e) {
			const pos = getPos();

			if (pos === undefined)
				return;

			view.dispatch(
				view.state.tr.setNodeMarkup(
					pos,
					null,
					{ ...node.attrs, id: (e.target as HTMLInputElement).value }
				)
			);
		};

		this.inputWrap.appendChild(this.input);
	}

    update(node: ProsemirrorNode) {
        if (node.type.name === 'heading' && node.attrs.level === this.contentDOM.tagName[1]) {
            this.contentDOM.id = node.attrs.id;
            this.input.value = node.attrs.id;
            
            return true;
        }

        return false;
    }

	stopEvent(e: Event) {
		return (e.target as Node).isEqualNode(this.input);
	}
}
