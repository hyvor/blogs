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
    columnSettings: HTMLElement;
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
        this.bottomSettings.setAttribute("contenteditable", "false");
        this.bottomSettings.className = "table-bottom-settings";

        this.topSettings = document.createElement("div");
        this.topSettings.setAttribute("contenteditable", "false");
        this.topSettings.className = "table-top-settings";

        this.columnSettings = document.createElement("div");
        this.columnSettings.setAttribute("contenteditable", "false");
        this.columnSettings.className = "table-column-settings";
        this.topSettings.appendChild(this.columnSettings);

        this.rightSideSettings = document.createElement("div");
        this.rightSideSettings.className = "table-right-side-settings";

        this.dom.appendChild(this.topSettings);

        this.leftSideSettings = document.createElement("div");
        this.leftSideSettings.setAttribute("contenteditable", "false");
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
        
        this.addRowBeforeWrapper = this.addRowBeforeWrapper.bind(this);
        this.addRowAfterWrapper = this.addRowAfterWrapper.bind(this);
        this.addColumnBeforeWrapper = this.addColumnBeforeWrapper.bind(this);
        this.addColumnAfterWrapper = this.addColumnAfterWrapper.bind(this);
        this.makeRowHeaderWrapper = this.makeRowHeaderWrapper.bind(this);
        this.makeColumnHeaderWrapper = this.makeColumnHeaderWrapper.bind(this);
        this.deleteRowWrapper = this.deleteRowWrapper.bind(this);
        this.deleteColumnWrapper = this.deleteColumnWrapper.bind(this);
        this.clearRowContentWrapper = this.clearRowContentWrapper.bind(this);
        this.clearColumnContentWrapper = this.clearColumnContentWrapper.bind(this);

        this.view.dom.addEventListener('click', this.handleChange);
        this.view.dom.addEventListener('keyup', this.handleChange);
    }

    handleChange = () => {
        this.createMenuItems();
    };
        
    addRowBeforeWrapper = () => {
        addRowBefore(this.view.state, this.view.dispatch);
        this.createMenuItems();
    }

    addRowAfterWrapper = () => {
        addRowAfter(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    addColumnBeforeWrapper = () => {
        addColumnBefore(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    addColumnAfterWrapper = () => {
        addColumnAfter(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    makeRowHeaderWrapper = () => {
        toggleHeaderRow(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    makeColumnHeaderWrapper = () => {
        toggleHeaderColumn(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    deleteRowWrapper = () => {
        deleteRow(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    deleteColumnWrapper = () => {
        deleteColumn(this.view.state, this.view.dispatch);
        this.createMenuItems();
    };

    clearRowContentWrapper = () => {
        const selection = this.view.state.selection;
        const row = selection.$from.node(-2);
        const rowPos = selection.$from.before(-2);
        const tr = this.view.state.tr;
        tr.replaceWith(rowPos, rowPos + row.nodeSize, this.schema.nodes.table_row.createAndFill()!);
        this.view.dispatch(tr);
        this.createMenuItems();
    }

    clearColumnContentWrapper = () => {
        const selection = this.view.state.selection;
        const tableCell = selection.$from.node(-1);
        let tableRow = selection.$from.node(-2);
        const table = selection.$from.node(-3);
        let parentIndex = -2;
        const tr = this.view.state.tr;
        if (tableRow.type.name !== 'table_row') {
            parentIndex--;
            tableRow = selection.$from.node(parentIndex);
        }
        let currentColumn = 0;
        // Get the column of the selected cell
        for (let i = 0; i < tableRow.childCount; i++) {
            if (tableRow.child(i) === tableCell) {
                currentColumn = i;
                break;
            }
        }
        console.log(currentColumn);
        for (let i = 0; i < table.childCount; i++) {
            for (let j = 0; j < tableRow.childCount; j++) {
                const cell = table.child(i).child(j);
                if (j === currentColumn) {
                    const cellPos = selection.$from.before(parentIndex) + tableRow.nodeSize + cell.nodeSize * i;
                    tr.replaceWith(cellPos, cellPos + cell.nodeSize, this.schema.nodes.table_cell.createAndFill()!);
                }
            }
        }
        this.view.dispatch(tr);
        this.createMenuItems();
    }

    createInside() {
        const _self = this;

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
        

        /*const deleteRowButton = document.createElement("button");
        deleteRowButton.className = "delete-row-button";
        deleteRowButton.innerText = "-";
        deleteRowButton.onclick = function () {
            const selection = _self.view.state.selection;
            const row = selection.$from.node(-2);
            for (let i = 0; i < row.childCount; i++) {
                const cell = row.child(i);
                // -2 for the offsetting the size
                if (cell.content.size - 2 > 0) {
                    deleteRowButton.setAttribute("disabled", "true");
                    return;
                }
                //deleteRowButton.setAttribute("disabled", "false");
            }
            deleteRow(_self.view.state, _self.view.dispatch);
            _self.createMenuItems();
            // If it remains the only row, delete the table
            const table = _self.view.state.doc.nodeAt(_self.getPos()!);
            if (table && table.childCount === 1) {
                deleteTable(_self.view.state, _self.view.dispatch);
            }   
        }
        this.bottomSettings.appendChild(deleteRowButton);*/

        const addColumnButton = document.createElement("button");
        addColumnButton.className = "add-column-button";
        addColumnButton.innerText = "+";
        addColumnButton.onclick = function () {
            addColumnAfter(_self.view.state, _self.view.dispatch);
        };
        this.rightSideSettings.appendChild(addColumnButton);

        /*const deleteColumnButton = document.createElement("button");
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
        this.rightSideSettings.appendChild(deleteColumnButton);*/
    }
    
    update(node: ProsemirrorNode) {
        if (node.type.name === 'table') {
            this.node = node;
            return true;
        }

        return false;
        
    }

    ignoreMutation(mutation: MutationRecord) {
        return true;
    }

    isRowFocused(row: ProsemirrorNode) {
        const selection = this.view.state.selection;
        const grandParent = selection.$from.node(-2);
        return grandParent === row;
    }

    isColumnFocused(columnIndex: number) {
        const selection = this.view.state.selection;
        const tableCell = selection.$from.node(-1);
        let tableRow = selection.$from.node(-2);
        let parentIndex = -3;
        if (tableRow.type.name !== 'table_row') {
            tableRow = selection.$from.node(parentIndex);
            parentIndex--;
        }
        for (let i = 0; i < tableRow.childCount; i++) {
            if (tableRow.child(i) === tableCell && i === columnIndex) {
                return true;
            }
        }
        return false;
    }

    createMenuItems() {
        const _self = this;
        const table = this.node;
        const rows = table.content.childCount;

        // Clear the left side settings
        while (this.leftSideSettings.firstChild) {
            this.leftSideSettings.removeChild(this.leftSideSettings.firstChild);
        }

        // Clear the top settings
        while (this.columnSettings.firstChild) {
            this.columnSettings.removeChild(this.columnSettings.firstChild);
        }

        // Create row menu items
        for (let rowIdx = 0; rowIdx < rows; rowIdx++) {
          const tableMenuWrapper = document.createElement("div");
          _self.leftSideSettings.appendChild(tableMenuWrapper);
            ReactDOM.render(
            <TableMenu 
                colunmMenu={false}
                focused={_self.isRowFocused(table.child(rowIdx))}
                addBefore={_self.addRowBeforeWrapper}
                addAfter={_self.addRowAfterWrapper}
                makeHeader={_self.makeRowHeaderWrapper}
                clearContent={_self.clearRowContentWrapper}
                deleteWrapper={_self.deleteRowWrapper}
            />, tableMenuWrapper);
        }

        // Create column menu items
        for (let colIdx = 0; colIdx < table.firstChild!.childCount; colIdx++) {
            const tableMenuWrapper = document.createElement("div");
            _self.columnSettings.appendChild(tableMenuWrapper);
            ReactDOM.render(
                <TableMenu 
                    colunmMenu={true}
                    focused={_self.isColumnFocused(colIdx)}
                    addBefore={_self.addColumnBeforeWrapper}
                    addAfter={_self.addColumnAfterWrapper}
                    makeHeader={_self.makeColumnHeaderWrapper}
                    clearContent={_self.clearColumnContentWrapper}
                    deleteWrapper={_self.deleteColumnWrapper}
                />, tableMenuWrapper);
        }
      }
}