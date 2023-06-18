import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";
import { Calendar2Minus, Calendar2Plus, Trash  } from 'react-bootstrap-icons';
import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import { EditorView, NodeView } from "prosemirror-view";
import {Node as ProsemirrorNode} from "prosemirror-model";
import Tooltip from "react-tooltip"
import ReactTooltip from "react-tooltip";


export default class Table implements NodeView{
    /*
    * TODO list for table
    - Fix bug when writting on the table, the table disapear somtimes
    - Add "Add bellow" and "Add under" for the rows (notion like)
    */

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;
    columnHeader: boolean;
    rowHeader: boolean;

    dom: HTMLElement;
  
    constructor(node: ProsemirrorNode, view: EditorView, getPos: { (): number | undefined; (): number | undefined; }) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.columnHeader = true; // Table create with column by default headers
        this.rowHeader = false; // Table create without row headers by default


        this.dom = document.createElement("table")
        this.renderNode(node, getPos);
    }

    ColumnHeaderButton = () => {
        const [columnHeader, setColumnHeader] = useState(this.columnHeader);
        const addColumnHeader = () => {
            this.addColumnHeaderButtonClick();
            setColumnHeader(true);
        }

        const deleteColumnHeader = () => {
            this.deleteColumnHeaderButtonClick();
            setColumnHeader(false);
        }
        return <>
                <button
                    data-tip={columnHeader ? "Remove column header" : "Add column header"}
                    className="icon-button column-header-button"
                    onClick={columnHeader ? deleteColumnHeader : addColumnHeader}>
                    {columnHeader ? <Calendar2Minus /> : <Calendar2Plus />}
                </button>
                <ReactTooltip place="bottom" type="light" effect="solid"/>
            </>
    }

    RowHeaderButton = () => {
        const [rowHeader, setRowHeader] = useState(this.rowHeader);
        const addRowHeader = () => {
            this.addRowHeaderButtonClick();
            setRowHeader(true);
        }

        const deleteRowHeader = () => {
            this.deleteRowHeaderButtonClick();
            setRowHeader(false);
        }
        return  <>
                    <button
                        data-tip={rowHeader ? "Remove row header" : "Add row header"}
                        className="icon-button row-header-button"
                        onClick={rowHeader ? deleteRowHeader : addRowHeader}>
                        {rowHeader ? <Calendar2Minus /> : <Calendar2Plus />}
                    </button>
                    <ReactTooltip place="bottom" type="light" effect="solid"/>
            </>
    }

    DeleteTableButton = () => {
        return <button
                className="icon-button delete-table-button"
                onClick={this.deleteTable}>
            <Trash />
        </button>
    }
    tip
    renderNode(node: ProsemirrorNode, pos: { (): number | undefined; (): number | undefined; }) {

        // Global div for the table and buttons
        const tableWrapper = document.createElement("div");
        tableWrapper.setAttribute("class", "table-wrapper");

        const tableColumn = document.createElement("div");
        tableColumn.setAttribute("class", "table-column");
        this.dom.innerHTML = "";

        // Start: Table settings row
        const tableSettingsRow = document.createElement("div");
        tableSettingsRow.setAttribute('class', 'table-settings hidden');

        ReactDOM.render(
            <>
                <this.ColumnHeaderButton />
                <this.RowHeaderButton />
                <this.DeleteTableButton />
            </>,
            tableSettingsRow
          );

        tableColumn.appendChild(tableSettingsRow);
        // End: Table settings row

        const table = document.createElement("table");
        table.setAttribute("class", "not-focused");
        table.contentEditable = 'true';
        const tbody = document.createElement("tbody");

        const headerRow = document.createElement("tr");
        headerRow.contentEditable = 'true';

        for (let i = 0; i < 2; i++) {
            const th = document.createElement("th");
            th.contentEditable = 'true';
            th.innerHTML = `Header ${i + 1}`;
            headerRow.appendChild(th);
        }
        tbody.appendChild(headerRow);

        for (let i = 0; i < 3; i++) {
            const tr = document.createElement("tr");
            tr.setAttribute("contentEditable", 'true');
            for (let j = 0; j < 2; j++) {
                const td = document.createElement("td");
                td.contentEditable = 'true';
                td.innerHTML = "<p> </p>";
                tr.appendChild(td);
            }

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);
        tableColumn.appendChild(table);
        tableWrapper.appendChild(tableColumn);

        // Div for rows buttons
        const rowsButtonsDiv = document.createElement("div");
        rowsButtonsDiv.setAttribute("class", "rows-buttons hidden");

        const addRowButton = document.createElement("button");
        addRowButton.setAttribute("class", "icon-button add-row-button");
        addRowButton.innerHTML = "+";
        addRowButton.addEventListener("click", this.addRowButtonClick);
        rowsButtonsDiv.appendChild(addRowButton);

        const deleteRowButton = document.createElement("button");
        deleteRowButton.innerHTML = "-";
        deleteRowButton.setAttribute("class", "icon-button delete-row-button");
        deleteRowButton.addEventListener("click", this.deleteRowButtonClick);
        rowsButtonsDiv.appendChild(deleteRowButton);

        tableColumn.appendChild(rowsButtonsDiv);

        // Div for column buttons
        const columnsButtonsDiv = document.createElement("div");
        columnsButtonsDiv.setAttribute("class", "columns-buttons hidden");

        const addColumnButton = document.createElement("button");
        addColumnButton.setAttribute("class", "icon-button add-row-button");
        addColumnButton.innerHTML = "+";
        addColumnButton.addEventListener("click", this.addColumnButtonClick);
        columnsButtonsDiv.appendChild(addColumnButton);

        const deleteColumnButton = document.createElement("button");
        deleteColumnButton.setAttribute("class", "icon-button delete-row-button");
        deleteColumnButton.innerHTML = "-";
        deleteColumnButton.addEventListener("click", this.deleteColumnButtonClick);
        columnsButtonsDiv.appendChild(deleteColumnButton);

        tableWrapper.appendChild(columnsButtonsDiv);

        // Append table wrapper to the DOM
        this.dom.appendChild(tableWrapper);

        /*const addColumnHeader = document.createElement("button");
        addColumnHeader.innerHTML = "Add column header";
        addColumnHeader.addEventListener("click", this.addColumnHeaderButtonClick);
        this.dom.appendChild(addColumnHeader);

        const deleteColumnHeader = document.createElement("button");
        deleteColumnHeader.innerHTML = "Delete column header";
        deleteColumnHeader.addEventListener("click", this.deleteColumnHeaderButtonClick);
        this.dom.appendChild(deleteColumnHeader);

        const addRowHeader = document.createElement("button");
        addRowHeader.innerHTML = "Add row header";
        addRowHeader.addEventListener("click", this.addRowHeaderButtonClick);
        this.dom.appendChild(addRowHeader);

        const deleteRowHeader = document.createElement("button");
        deleteRowHeader.innerHTML = "Delete row header";
        deleteRowHeader.addEventListener("click", this.deleteRowHeaderButtonClick);
        this.dom.appendChild(deleteRowHeader);*/


        // TODO: fix the focus of the table when row and column are added/deleted
        table.addEventListener("focus", () => {
            tableSettingsRow.classList.remove("hidden");
            rowsButtonsDiv.classList.remove("hidden");
            columnsButtonsDiv.classList.remove("hidden");
            table.classList.remove("not-focused");
            table.classList.add("focused");
          });
          
          table.addEventListener("blur", async () => {
            // Add little delay to allow button to be clicked
            await new Promise((resolve) => setTimeout(resolve, 200));
            tableSettingsRow.classList.add("hidden");
            rowsButtonsDiv.classList.add("hidden");
            columnsButtonsDiv.classList.add("hidden");
            table.classList.add("not-focused");
            table.classList.remove("focused");
          });
    }

    addRowButtonClick = (event: { preventDefault: () => void; }) => {
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const newRow = document.createElement("tr");
        newRow.setAttribute("contentEditable", 'true');
      
        for (let i = 0; i < rows[0].children.length; i++) {
          const td = document.createElement("td");
          const p = document.createElement("p");
          td.setAttribute("contentEditable", 'true');
          p.innerHTML = " ";
          p.setAttribute("contentEditable", 'true');
          td.appendChild(p);
          newRow.appendChild(td);
        }
      
        tbody!.appendChild(newRow);
        const firstCell = newRow.querySelector("td");
        firstCell!.focus();
        event.preventDefault();
      }
      
      
    deleteRowButtonClick = () => {
        // When it remains only one row, delete the table
        if (this.dom.querySelector("tbody")!.querySelectorAll("tr").length === 2) {
            this.deleteTable();
            return;
        }
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const lastRow = rows[rows.length - 1];
        lastRow.remove();
    }

    addColumnButtonClick = () => {
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const headerRow = rows[0];
        const newHeaderCell = document.createElement("th");
        newHeaderCell.setAttribute("contentEditable", 'true');
        newHeaderCell.innerHTML = `Header ${headerRow.children.length + 1}`;
        headerRow.appendChild(newHeaderCell);

        for (let i = 1; i < rows.length; i++) {
            const td = document.createElement("td");
            td.setAttribute("contentEditable", 'true');
            td.innerHTML = "<p> </p>";
            rows[i].appendChild(td);
        }
    }

    deleteColumnButtonClick = () => {
        // When it remains only one column, delete the table
        if (this.dom.querySelector("table")!.querySelector("tbody")!.querySelector("tr")!.children.length === 1) {
            this.deleteTable();
            return;
        }
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const headerRow = rows[0];
        const lastHeaderCell = headerRow.children[headerRow.children.length - 1];
        lastHeaderCell.remove();

        for (let i = 1; i < rows.length; i++) {
            const lastCell = rows[i].children[rows[i].children.length - 1];
            lastCell.remove();
        }
    }

    addColumnHeaderButtonClick = () => {
        // Add header only when it doesn't exist
        if (this.columnHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const firstRow = rows[0];
        const newRow = document.createElement("tr");
        newRow.setAttribute("contentEditable", 'true');
        for (let i = 0; i < firstRow.children.length; i++) {
            const th = document.createElement("th");
            th.setAttribute("contentEditable", 'true');
            th.innerHTML = `Header ${i + 1}`;
            newRow.appendChild(th);
        }
        tbody!.insertBefore(newRow, firstRow);
        this.columnHeader = true;
    }

    deleteColumnHeaderButtonClick = () => {
        // Delete header only when it exists
        if (!this.columnHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        const headerRow = rows[0];
        this.columnHeader = false;
        headerRow.remove();
    }

    addRowHeaderButtonClick = () => {
        // Add header only when it doesn't exist
        if (this.rowHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        for (let i = 0; i < rows.length; i++) {
            const th = document.createElement("th");
            th.setAttribute("contentEditable", 'true');
            th.innerHTML = `Header ${i + 1}`;
            rows[i].insertBefore(th, rows[i].children[0]);
        }
        this.rowHeader = true;
    }

    deleteRowHeaderButtonClick = () => {
        // Delete header only when it exists
        if (!this.rowHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table!.querySelector("tbody");
        const rows = tbody!.querySelectorAll("tr");
        for (let i = 0; i < rows.length; i++) {
            rows[i].children[0].remove();
        }
        this.rowHeader = false;
    }

    deleteTable = () => {
        const { state, dispatch } = this.view;
        const tr = state.tr;
        if (this.getPos() === undefined)
            return;
        dispatch(tr.delete(this.getPos(), this.getPos() + 1));
    }
}
