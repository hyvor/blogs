import {EditorView, NodeView} from "prosemirror-view";
import type {Node as ProsemirrorNode, Schema} from 'prosemirror-model';

export default class Toc implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        this.dom = document.createElement('div');
        this.contentDOM = document.createElement('div');
        
        const headings = [];
        console.log(view.state.doc);
        /*for (let i = 0; i < view.state.doc.content.size; i++) {
            const node = view.state.doc.content.child(i);
            if (node.type.name === 'heading') {
                headings.push(node);
            }
        }*/
    }

}
