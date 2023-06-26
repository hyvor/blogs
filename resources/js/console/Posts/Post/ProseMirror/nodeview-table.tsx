import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import { Trash } from "react-bootstrap-icons";
import ReactDOM from "react-dom";
import React from "react";
import {
    addColumnAfter,
    addColumnBefore,
    deleteColumn,
    addRowAfter,
    addRowBefore,
    deleteRow,
    mergeCells,
    splitCell,
    setCellAttr,
    toggleHeaderRow,
    toggleHeaderColumn,
    toggleHeaderCell,
    goToNextCell,
    deleteTable,
  } from "prosemirror-tables";



export default class Table implements NodeView{

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    bottomSettings: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;
        
        this.dom = document.createElement("div");
        this.dom.className = "table-wrapper";

        this.bottomSettings = document.createElement("div");
        this.bottomSettings.className = "table-bottom-settings";

        this.createInside = this.createInside.bind(this);
        this.createInside();

        this.contentDOM = document.createElement("table");
        this.contentDOM.className = "table-div";
        const id = node.attrs.id || "";
        this.contentDOM.id = id;
        this.dom.appendChild(this.contentDOM);

        this.dom.appendChild(this.bottomSettings);
    }

    createInside() {
        const _self = this;
        const deleteButton = document.createElement("button");
        deleteButton.className = "icon-button delete-table-button";
        ReactDOM.render(<Trash />, deleteButton);
        deleteButton.onclick = function () {
            deleteTable(_self.view.state, _self.view.dispatch);
        };
        this.dom.appendChild(deleteButton);

        const addRowButton = document.createElement("button");
        addRowButton.className = "add-row-button";
        addRowButton.innerText = "+";
        addRowButton.onclick = function () {
            addRowAfter(_self.view.state, _self.view.dispatch);
        };
        this.bottomSettings.appendChild(addRowButton);
        

        const deleteRowButton = document.createElement("button");
        deleteRowButton.className = "delete-row-button";
        deleteRowButton.innerText = "-";
        deleteRowButton.onclick = function () {
            deleteRow(_self.view.state, _self.view.dispatch);
        }
        this.bottomSettings.appendChild(deleteRowButton);
    }


}