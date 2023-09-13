import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode, Schema} from "prosemirror-model";
import { ArrowsCollapse, ArrowsExpand, Trash } from "react-bootstrap-icons";
import ReactDOM from "react-dom/client";
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
    deleteTable, updateColumnsOnResize,
} from "prosemirror-tables";
import { EditorState, NodeSelection, TextSelection } from "prosemirror-state";
import { createRoot } from "react-dom/client";
import TableMenu from "./TableMenu";
import Tooltip from "../../../ReusableComponents/Tooltip";



export default class Table implements NodeView{

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    schema: Schema;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    table: HTMLTableElement;
    colgroup: HTMLTableColElement;

    private minColWidth = 20;

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

        this.rightSideSettings = document.createElement("div");
        this.rightSideSettings.className = "table-right-side-settings";
        this.rightSideSettings.setAttribute("contenteditable", "false");

        this.dom.appendChild(this.topSettings);

        this.leftSideSettings = document.createElement("div");
        this.leftSideSettings.setAttribute("contenteditable", "false");
        this.leftSideSettings.className = "table-left-side-settings";

        this.createInside = this.createInside.bind(this);
        this.createInside();

        this.middle = document.createElement("div");
        this.middle.className = "table-middle";
        this.dom.appendChild(this.columnSettings);
        this.dom.appendChild(this.middle);

        this.table = document.createElement("table");
        this.colgroup = document.createElement("colgroup");
        this.contentDOM = document.createElement("tbody");

        this.table.appendChild(this.colgroup);
        this.table.appendChild(this.contentDOM);

        updateColumnsOnResize(node, this.colgroup, this.table, this.minColWidth);

        this.middle.appendChild(this.leftSideSettings);
        this.middle.appendChild(this.table);
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
        this.focusTable = this.focusTable.bind(this);

        this.dom.addEventListener('click', this.handleChange);
        //this.view.dom.addEventListener('keyup', this.handleChange);

        this.middle.addEventListener('scroll', () => {this.columnSettings.setAttribute("style", `display: none;`)});
        this.middle.addEventListener('click', () => {this.columnSettings.setAttribute("style", `display: flex;`)});
    }

    handleChange = () => {
        this.createMenuItems();
    };
        
    addRowBeforeWrapper = () => {
        addRowBefore(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    }

    addRowAfterWrapper = () => {
        addRowAfter(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    addColumnBeforeWrapper = () => {
        addColumnBefore(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    addColumnAfterWrapper = () => {
        addColumnAfter(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    makeRowHeaderWrapper = () => {
        toggleHeaderRow(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    makeColumnHeaderWrapper = () => {
        toggleHeaderColumn(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    deleteRowWrapper = () => {
        deleteRow(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    deleteColumnWrapper = () => {
        deleteColumn(this.view.state, this.view.dispatch);
        this.createMenuItems();
        this.focusTable();
    };

    clearRowContentWrapper = () => {
        const selection = this.view.state.selection;
        const row = selection.$from.node(-2);
        const rowPos = selection.$from.before(-2);
        const tr = this.view.state.tr;
        tr.replaceWith(rowPos, rowPos + row.nodeSize, this.schema.nodes.table_row.createAndFill()!);
        this.view.dispatch(tr);
        this.createMenuItems();
        this.focusTable();
    }

    clearColumnContentWrapper = () => {
        const selection = this.view.state.selection;
        const tableCell = selection.$from.node(-1);
        let tableRow = selection.$from.node(-2);
        const table = selection.$from.node(-3);
        const tablePos = selection.$from.before(-3);
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

        let offset = tablePos + 2;
        for (let i = 0; i < table.childCount; i++) {
            for (let j = 0; j < tableRow.childCount; j++) {
                const cell = table.child(i).child(j);
                const cellSize = cell.nodeSize;
                if (j === currentColumn) {
                    const currentCellPos = offset;
                    tr.replaceWith(currentCellPos, currentCellPos + cell.nodeSize, this.schema.nodes.table_cell.createAndFill()!);
                    offset += 4; // Size of an empty table cell
                } else
                    offset += cellSize;
            }
            offset += 2;
        }
        this.view.dispatch(tr);
        this.createMenuItems();
        this.focusTable();
    }

    // Focus back the table
    focusTable() {
        /*let { $from, to } = this.view.state.selection,
                    pos;
        let same = $from.sharedDepth(to);
        pos = $from.before(same);
        this.view.dispatch(
            this.view.state.tr.setSelection(
                NodeSelection.create(this.view.state.doc, pos)
        ));*/
    };

    createInside() {
        const _self = this;

        const mergeCellButton = document.createElement("button");
        mergeCellButton.className = "icon-button merge-cell-button";
        mergeCellButton.onclick = function () {
            mergeCells(_self.view.state, _self.view.dispatch);
            _self.createMenuItems();
        };
        let root = ReactDOM.createRoot(mergeCellButton);
        root.render(
            <div>
                <Tooltip tooltip="Merge cells">
                    <ArrowsCollapse className="table-icon"/>
                </Tooltip>
            </div>
        );
        this.topSettings.appendChild(mergeCellButton);

        const splitCellButton = document.createElement("button");
        splitCellButton.className = "icon-button split-cell-button";
        splitCellButton.onclick = function () {
            splitCell(_self.view.state, _self.view.dispatch);
            _self.createMenuItems();
        };
        root = ReactDOM.createRoot(splitCellButton);
        root.render(
            <div>
                <Tooltip tooltip="Split cells">
                    <ArrowsExpand className="table-icon"/>
                </Tooltip>
            </div>
        );
        this.topSettings.appendChild(splitCellButton);

        const deleteButton = document.createElement("button");
        deleteButton.setAttribute("id", "delete-table-button");
        deleteButton.className = "icon-button delete-table-button";

        root = ReactDOM.createRoot(deleteButton);
        root.render(
            <div>
                <Tooltip tooltip="Delete table">
                    <Trash className="table-icon"/>
                </Tooltip>
            </div>
        );

        deleteButton.onclick = function () {
            deleteTable(_self.view.state, _self.view.dispatch);
        };
        this.topSettings.appendChild(deleteButton);

        const addRowButton = document.createElement("button");
        addRowButton.className = "add-row-button";
        addRowButton.innerText = "+";
        addRowButton.onclick = function () {
            const currentPos = _self.getPos()!;
            let tr = _self.view.state.tr;
            tr.setSelection(
                NodeSelection.create(
                    _self.view.state.doc,
                    currentPos
                )
            );
            _self.view.dispatch(tr);
            addRowAfter(_self.view.state, _self.view.dispatch);
            let tr2 = _self.view.state.tr;
            let firstCellLastRow = currentPos;
            for (let i = 0; i < _self.node.childCount - 1; i++) {
                firstCellLastRow += _self.node.child(i).nodeSize;
            }
            const nodeAt= _self.view.state.doc.nodeAt(firstCellLastRow + 2);
            console.log(nodeAt);
            _self.view.dispatch(
                tr2.setSelection(
                    TextSelection.create(
                        _self.view.state.doc,
                        firstCellLastRow + 2
                    )
            ));
           _self.view.dispatch(tr2);
           _self.createMenuItems();
        };
        this.bottomSettings.appendChild(addRowButton);

        const addColumnButton = document.createElement("button");
        addColumnButton.className = "add-column-button";
        addColumnButton.innerText = "+";
        addColumnButton.onclick = function () {
            let tr = _self.view.state.tr;
            const currentPos = _self.getPos()!;
            tr.setSelection(
                NodeSelection.create(
                    _self.view.state.doc,
                    currentPos
                )
            );
            _self.view.dispatch(tr);
            addColumnAfter(_self.view.state, _self.view.dispatch);
            const scrollableDiv = document.getElementsByClassName('table-middle').item(0) as HTMLDivElement;
            scrollableDiv.scrollLeft = scrollableDiv.scrollWidth - scrollableDiv.clientWidth;
            let firstCellLastColumn = currentPos;
            const firstRow = _self.node.child(0);
            for (let i = 0; i < firstRow.childCount; i++) {
                firstCellLastColumn += firstRow.child(i).nodeSize;
            }

            let tr2 = _self.view.state.tr;
            tr2.setSelection(
                TextSelection.create(
                    _self.view.state.doc,
                    firstCellLastColumn - 1
                )
            );
            _self.view.dispatch(tr2);
            _self.createMenuItems();
        };
        this.rightSideSettings.appendChild(addColumnButton);
    }
    
    update(node: ProsemirrorNode) {
        if (node.type.name === 'table') {
            this.node = node;
            updateColumnsOnResize(node, this.colgroup, this.table, this.minColWidth);
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

    stopEvent() {
        return false;
    }

    isColumnFocused(columnIndex: number) {
        const table = this.node;
        const rows = table.content.childCount;
        let focus = false;
        for (let rowIdx = 0; rowIdx < rows; rowIdx++) {
            if (this.isRowFocused(table.child(rowIdx))) {
                focus = true;
                break;
            }
        }
        if (!focus)
            return false;

        const selection = this.view.state.selection;
        const tableCell = selection.$from.node(-1);
        let tableRow = selection.$from.node(-2);
        if (!tableRow)
            return false;
        let parentIndex = -3;
        if (tableRow.type.name !== 'table_row') {
            tableRow = selection.$from.node(parentIndex);
            parentIndex--;
        }
        if (!tableRow)
            return false;
        for (let i = 0; i < tableRow.childCount; i++) {
            if (tableRow.child(i) === tableCell && i === columnIndex) {
                return true;
            }
        }
        return false;
    }

    createMenuItems() {
        console.log('table menu items created')
        const _self = this;
        const table = this.node;
        const rows = table.content.childCount;
        const selection = this.view.state.selection;

        // Clear the left side settings
        while (this.leftSideSettings.firstChild) {
            this.leftSideSettings.removeChild(this.leftSideSettings.firstChild);
        }

        // Clear the top settings
        while (this.columnSettings.firstChild) {
            this.columnSettings.removeChild(this.columnSettings.firstChild);
        }

        // Create row menu items
        const rowsInfo = _self.contentDOM.getElementsByTagName("tr");
        let rowCSSOffset = 0;
        for (let rowIdx = 0; rowIdx < rows; rowIdx++) {
            const tableMenuWrapper = document.createElement("div");
            _self.leftSideSettings.appendChild(tableMenuWrapper);
            let root = ReactDOM.createRoot(tableMenuWrapper);
            if (rowsInfo.item(rowIdx))
                rowCSSOffset += rowsInfo.item(rowIdx)!.clientHeight / 2 - 15;
            root.render(<TableMenu
                colunmMenu={false}
                focused={_self.isRowFocused(table.child(rowIdx))}
                addBefore={_self.addRowBeforeWrapper}
                addAfter={_self.addRowAfterWrapper}
                makeHeader={_self.makeRowHeaderWrapper}
                clearContent={_self.clearRowContentWrapper}
                deleteWrapper={_self.deleteRowWrapper}
                cssOffset={rowCSSOffset}/>)
            if (rowsInfo.item(rowIdx))
                rowCSSOffset += rowsInfo[rowIdx].clientHeight / 2 + 15;
        }

        // Create column menu items
        let colSSOffset = 0;
        const rowInfo = rowsInfo.item(0);
        for (let colIdx = 0; colIdx < table.firstChild!.childCount; colIdx++) {
            const tableMenuWrapper = document.createElement("div");
            _self.columnSettings.appendChild(tableMenuWrapper);
            let root = ReactDOM.createRoot(tableMenuWrapper);
            let cellWidth = 0
            if (rowInfo)
            {
                const cell = rowInfo.children.item(colIdx);
                if (cell)
                    cellWidth = cell.clientWidth;
            }
            colSSOffset += cellWidth / 2 - 17.5;
            root.render(<TableMenu 
                colunmMenu={true}
                focused={_self.isColumnFocused(colIdx)}
                addBefore={_self.addColumnBeforeWrapper}
                addAfter={_self.addColumnAfterWrapper}
                makeHeader={_self.makeColumnHeaderWrapper}
                clearContent={_self.clearColumnContentWrapper}
                deleteWrapper={_self.deleteColumnWrapper}
                cssOffset={colSSOffset - this.middle.scrollLeft}/>)
            colSSOffset += cellWidth / 2 + 17.5;
        }
      }
}