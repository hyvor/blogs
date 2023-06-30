import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import { Trash } from "react-bootstrap-icons";
import ReactDOM from "react-dom";
import React, { StrictMode } from "react";
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
import { NodeSelection } from "prosemirror-state";
import { createRoot } from "react-dom/client";
import TableMenu from "./TableMenu";



export default class Table implements NodeView{

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    middle: HTMLElement;
    topSettings: HTMLElement;
    bottomSettings: HTMLElement;
    leftSideSettings: HTMLElement;
    rightSideSettings: HTMLElement;

    constructor(schema: Schema, node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.schema = schema;
        
        this.dom = document.createElement("div");
        this.dom.className = "table-wrapper";

        this.bottomSettings = document.createElement("div");
        this.bottomSettings.className = "table-bottom-settings";

        this.topSettings = document.createElement("div");
        this.topSettings.className = "table-top-settings";

        this.rightSideSettings = document.createElement("div");
        this.rightSideSettings.className = "table-right-side-settings";

        this.dom.appendChild(this.topSettings);

        this.leftSideSettings = document.createElement("div");
        this.leftSideSettings.className = "table-left-side-settings";

        this.createInside = this.createInside.bind(this);
        this.createInside();

        this.middle = document.createElement("div");
        this.middle.className = "table-middle";
        this.dom.appendChild(this.middle);

        this.contentDOM = document.createElement("table");
        this.contentDOM.className = "table-div";
        const id = node.attrs.id || "";
        this.contentDOM.id = id;
        this.middle.appendChild(this.leftSideSettings);
        this.middle.appendChild(this.contentDOM);
        this.middle.appendChild(this.rightSideSettings);

        this.dom.appendChild(this.bottomSettings);
        this.createMenuItems();
    
    }

    handleFocusChange = () => {
        this.createMenuItems();
      };
      
      handleSelectionChange = () => {
        this.createMenuItems();
      };
      
      handleKeyDown = () => {
        this.createMenuItems();
      };
      
      handleClick = () => {
        this.createMenuItems();
      };

    createInside() {
        const _self = this;

        const toggleRowHeader = document.createElement("button");
        toggleRowHeader.className = "button add-header-row-button";
        toggleRowHeader.innerText = "Toggle Row Header";
        toggleRowHeader.onclick = function () {
            toggleHeaderRow(_self.view.state, _self.view.dispatch);
        };
        this.topSettings.appendChild(toggleRowHeader);

        const toggleColumnHeader = document.createElement("button");
        toggleColumnHeader.className = "button add-header-column-button";
        toggleColumnHeader.innerText = "Toggle Column Header";
        toggleColumnHeader.onclick = function () {
            toggleHeaderColumn(_self.view.state, _self.view.dispatch);
        };
        this.topSettings.appendChild(toggleColumnHeader);

        const deleteButton = document.createElement("button");
        deleteButton.setAttribute("id", "delete-table-button");
        deleteButton.className = "icon-button delete-table-button";

        ReactDOM.render(<Trash />, deleteButton);
        deleteButton.onclick = function () {
            deleteTable(_self.view.state, _self.view.dispatch);
        };
        this.topSettings.appendChild(deleteButton);

        const addRowButton = document.createElement("button");
        addRowButton.className = "add-row-button";
        addRowButton.innerText = "+";
        addRowButton.onclick = function () {
            addRowAfter(_self.view.state, _self.view.dispatch);
            _self.createMenuItems();

        };
        this.bottomSettings.appendChild(addRowButton);
        

        const deleteRowButton = document.createElement("button");
        deleteRowButton.className = "delete-row-button";
        deleteRowButton.innerText = "-";
        deleteRowButton.onclick = function () {
            deleteRow(_self.view.state, _self.view.dispatch);
            _self.createMenuItems();
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
        this.rightSideSettings.appendChild(addColumnButton);

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
        this.rightSideSettings.appendChild(deleteColumnButton);
    }

    isRowFocused(row: ProsemirrorNode) {
        const selection = this.view.state.selection;
        const grandParent = selection.$from.node(-2);
        return grandParent === row;
    }

    createMenuItems() {
        const _self = this;
        const table = this.node;
        const rows = table.content.childCount;
      
        // Remove existing menu items
        this.leftSideSettings.innerHTML = '';
      
        // Get the current selected row (if any)
        const selection = this.view.state.selection;
        const selectedRow = selection.$from.node(selection.$from.depth - 2); 
      
        // Create a button for each row
        for (let rowIdx = 0; rowIdx < rows; rowIdx++) {
          const row = table.content.child(rowIdx);
          const isFocused = this.isRowFocused(row);
      
          const uploader = <TableMenu
                   isFocused={isFocused}
                   selectedRow={selectedRow}
                />;
                ReactDOM.render(uploader, this.leftSideSettings);
            
        }
      }
      
    
}