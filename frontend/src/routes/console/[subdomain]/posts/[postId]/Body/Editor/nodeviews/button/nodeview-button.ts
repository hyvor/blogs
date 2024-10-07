import type { EditorView, NodeView } from "prosemirror-view";
import { type Node as ProsemirrorNode } from 'prosemirror-model';

export default class ButtonNodeView implements NodeView {
    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;

    dom: HTMLElement;
    contentDOM: HTMLElement;
    link: HTMLAnchorElement;

    constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("div");
        this.dom.className = "button-wrap";

        this.link = document.createElement("a");
        this.link.target = "_blank";
        this.link.href = node.attrs.href || "https://example.com";
        this.dom.appendChild(this.link);

        this.contentDOM = document.createElement("div");
        this.contentDOM.className = "content-div";

        this.link.appendChild(this.contentDOM);

        this.updateFromAttrs();
    }

    updateFromAttrs() {
        // Update the link href from the node's attributes
        this.link.href = this.node.attrs.href || "https://example.com";
    }

    update(node: ProsemirrorNode) {
        if (node.type.name === 'button') {
            this.node = node;
            this.updateFromAttrs();

            // Delete the node if it's empty
            if (node.content.size == 0) {
                const pos = this.getPos();
                if (pos !== undefined) {
                    const tr = this.view.state.tr;
                    tr.delete(pos, pos + node.nodeSize);
                    this.view.dispatch(tr);
                }
                return true;
            }

            return true;
        }

        return false;
    }

    changeAttr(name: string, value: string) {
        const attrs = { ...this.node.attrs, [name]: value };
        const pos = this.getPos();

        if (pos === undefined) return;

        this.view.dispatch(
            this.view.state.tr.setNodeMarkup(
                pos,
                undefined,
                attrs
            )
        );
    }

}
