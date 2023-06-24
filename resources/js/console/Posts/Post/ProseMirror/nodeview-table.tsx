import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import { Trash } from "react-bootstrap-icons";
import ReactDOM from "react-dom";
import React from "react";



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
        
        this.dom = document.createElement("div");
        this.dom.className = "table-wrapper";

        this.createInside = this.createInside.bind(this);
        this.createInside();

        this.contentDOM = document.createElement("table");
        this.contentDOM.className = "table-div";
        const id = node.attrs.id || "";
        this.contentDOM.id = id
        this.dom.appendChild(this.contentDOM);
    }

    createInside() {
        const _self = this;
        const deleteButton = document.createElement("button");
        deleteButton.className = "icon-button delete-table-button";
        ReactDOM.render(<Trash />, deleteButton);
        deleteButton.onclick = function () {
            _self.view.dispatch(_self.view.state.tr.delete(_self.getPos()!, _self.getPos()! + _self.node.nodeSize));
        };
        this.dom.appendChild(deleteButton);

        const addRowButton = document.createElement("button");
        addRowButton.className = "icon-button add-row-button";
        addRowButton.innerText = "Add Row";
        addRowButton.onclick = function () {
            const { tr } = _self.view.state;
            const pos = _self.getPos()! + 1;
            const row = _self.schema.nodes.table_row.createAndFill();
            if (row) {
                tr.insert(pos, row);
                _self.view.dispatch(tr);
            }
        };
        //this.dom.appendChild(addRowButton);
        
    }


}