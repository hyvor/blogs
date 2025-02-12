import type { Node as ProsemirrorNode } from 'prosemirror-model';
import { EditorView, type NodeView } from 'prosemirror-view';

export default class HeadingNodeView implements NodeView {

	dom: HTMLElement;
	contentDOM: HTMLElement;

	private selection: any;
	private input: HTMLInputElement;
	private inputWrap: HTMLDivElement;

	private node: ProsemirrorNode;

	constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

		this.node = node;

		this.dom = document.createElement('div');
		this.dom.classList.add('heading-wrap');

		const headingDetails = document.createElement('div');
		headingDetails.classList.add('heading-details');	

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

				console.log(this.node.attrs);
				tr.setNodeMarkup(getPos()!, null, { ...this.node.attrs, level });
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
		headingDetails.appendChild(headingSelectorsWrap);

		// Create inputWrap for the input field
		this.inputWrap = document.createElement("div");
		this.inputWrap.classList.add("input-wrap");
		this.inputWrap.contentEditable = "false";
		headingDetails.appendChild(this.inputWrap);
		this.dom.appendChild(headingDetails);

		// Create contentDOM
		this.contentDOM = document.createElement('h' + this.node.attrs.level);
		const id = this.node.attrs.id || "";
		this.contentDOM.id = id;
		this.dom.appendChild(this.contentDOM);

		
		const type = document.createElement("span");
		type.innerHTML = "#";
		this.inputWrap.appendChild(type);

		// ID input
		this.input = document.createElement("input");
		this.input.value = id;

		this.input.oninput = (e) => {
			const pos = getPos();

			if (pos === undefined)
				return;

			view.dispatch(
				view.state.tr.setNodeMarkup(
					pos,
					null,
					{ ...this.node.attrs, id: (e.target as HTMLInputElement).value }
				)
			);
		};

		this.inputWrap.appendChild(this.input);
		
	}

    update(node: ProsemirrorNode) {

		if (node.type.name !== 'heading') {
			return false;
		}

		this.node = node;

        if (Number(node.attrs.level) === Number(this.contentDOM.tagName[1])) {
            // changing ID
			
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