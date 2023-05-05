import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode} from 'prosemirror-model';

export default class Heading implements NodeView {

    dom: HTMLElement;
    contentDOM: HTMLElement;

    inputWrap: HTMLDivElement;
    input: HTMLInputElement;

    constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

        this.dom = document.createElement("div");
        this.dom.className = "heading-wrap";

        this.contentDOM = document.createElement("h" + node.attrs.level)
        const id = node.attrs.id || "";
        this.contentDOM.id = id
        this.dom.appendChild(this.contentDOM);

        this.inputWrap = document.createElement("div");
        this.inputWrap.contentEditable = "false";
        this.dom.appendChild(this.inputWrap)

        const type = document.createElement("span");
        type.innerHTML = "h" + node.attrs.level + "#"
        this.inputWrap.appendChild(type)

        // id input
        this.input = document.createElement("input");
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
