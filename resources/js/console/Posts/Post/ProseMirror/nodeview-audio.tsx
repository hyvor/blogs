import ReactDOM from 'react-dom';
import React from 'react';
import {NodeSelection, TextSelection} from "prosemirror-state";
import { Node, Node as ProsemirrorNode, Schema } from "prosemirror-model";
import { EditorView, NodeView } from "prosemirror-view";

export default class Audio implements NodeView {
    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {

        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;

        const wrap = document.createElement("div");
        wrap.className = "audio-wrap";

        const audio = document.createElement("audio");
        audio.setAttribute("controls", "controls");
        audio.setAttribute("src", node.attrs.src);
        wrap.appendChild(audio);

        this.dom = wrap;
    }
}