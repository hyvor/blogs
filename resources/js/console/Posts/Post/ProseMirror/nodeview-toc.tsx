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
    }

}
