import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";
import { Calendar2Minus, Calendar2Plus, Trash  } from 'react-bootstrap-icons';
import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import Tooltip from "react-tooltip"
import ReactTooltip from "react-tooltip";


export default class Table implements NodeView{

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
        
        this.dom = document.createElement("aside")

        this.contentDOM = document.createElement("table")
        this.contentDOM.className = "table-div";
        const id = node.attrs.id || "";
        this.contentDOM.id = id
        this.dom.appendChild(this.contentDOM);
    }
}