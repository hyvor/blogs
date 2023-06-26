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

    middle: HTMLElement;
    bottomSettings: HTMLElement;
    sideSettings: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;
        
        this.dom = document.createElement("div");
        this.dom.className = "table-wrapper";

        this.bottomSettings = document.createElement("div");
        this.bottomSettings.className = "table-bottom-settings";

        this.sideSettings = document.createElement("div");
        this.sideSettings.className = "table-side-settings";

        this.createInside = this.createInside.bind(this);
        this.createInside();

        this.middle = document.createElement("div");
        this.middle.className = "table-middle";
        this.dom.appendChild(this.middle);

        this.contentDOM = document.createElement("table");
        this.contentDOM.className = "table-div";
        const id = node.attrs.id || "";
        this.contentDOM.id = id;
        this.middle.appendChild(this.contentDOM);
        this.middle.appendChild(this.sideSettings);

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
            // If it remains the only row, delete the table
            const table = _self.view.state.doc.nodeAt(_self.getPos()!);
            if (table && table.childCount === 1) {
                deleteTable(_self.view.state, _self.view.dispatch);
            }   
        }
        this.bottomSettings.appendChild(deleteRowButton);

        const addColumnButton = document.createElement("button");
        addColumnButton.className = "add-column-button";
        addColumnButton.innerText = "+";
        addColumnButton.onclick = function () {
            addColumnAfter(_self.view.state, _self.view.dispatch);
        };
        this.sideSettings.appendChild(addColumnButton);

        const deleteColumnButton = document.createElement("button");
        deleteColumnButton.className = "delete-column-button";
        deleteColumnButton.innerText = "-";
        deleteColumnButton.onclick = function () {
            deleteColumn(_self.view.state, _self.view.dispatch);
            // If it remains the only column, delete the table
            const table = _self.view.state.doc.nodeAt(_self.getPos()!);
            if (table && table.firstChild && table.firstChild.childCount === 1) {
                deleteTable(_self.view.state, _self.view.dispatch);
            }
        };
        this.sideSettings.appendChild(deleteColumnButton);
        
    }


}