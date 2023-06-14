import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";
import { Trash } from 'react-bootstrap-icons';

export default class Table {
  
    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.columnHeader = true; // Table create with column by default headers
        this.rowHeader = false; // Table create without row headers by default


        this.dom = document.createElement("table")
        this.renderNode(node, getPos);
    }

    renderNode(node, pos) {
        const div = document.createElement("div");
        div.setAttribute("class", "table-wrapper");
        this.dom.innerHTML = "";
    
        // TODO: show delete button only when node is focused
       // TODO: show delete button only when node is focused
        const deleteTableButton = document.createElement("button");
        deleteTableButton.setAttribute("class", "icon-button delete-table-button hidden"); // Add the "hidden" class initially
        deleteTableButton.innerHTML = "X";        
        deleteTableButton.addEventListener("click", this.deleteTable);
        div.appendChild(deleteTableButton);

        const table = document.createElement("table");
        table.setAttribute("class", "not-focused");
        table.setAttribute("contenteditable", true);
        const tbody = document.createElement("tbody");

        const headerRow = document.createElement("tr");
        headerRow.setAttribute("contenteditable", true);

        for (let i = 0; i < 2; i++) {
            const th = document.createElement("th");
            th.setAttribute("contenteditable", true);
            th.innerHTML = `Header ${i + 1}`;
            headerRow.appendChild(th);
        }
        tbody.appendChild(headerRow);

        for (let i = 0; i < 3; i++) {
            const tr = document.createElement("tr");
            tr.setAttribute("contenteditable", true);
            for (let j = 0; j < 2; j++) {
                const td = document.createElement("td");
                td.setAttribute("contenteditable", true);
                td.innerHTML = "<p> </p>";
                tr.appendChild(td);
            }

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);
        div.appendChild(table);

        this.dom.appendChild(div);

        this.dom.addEventListener("click", this.selectNode);

        const addRowButton = document.createElement("button");
        addRowButton.innerHTML = "Add row";
        addRowButton.addEventListener("click", this.addRowButtonClick);
        this.dom.appendChild(addRowButton);

        const deleteRowButton = document.createElement("button");
        deleteRowButton.innerHTML = "Delete row";
        deleteRowButton.addEventListener("click", this.deleteRowButtonClick);
        this.dom.appendChild(deleteRowButton);

        const addColumnButton = document.createElement("button");
        addColumnButton.innerHTML = "Add column";
        addColumnButton.addEventListener("click", this.addColumnButtonClick);
        this.dom.appendChild(addColumnButton);

        const deleteColumnButton = document.createElement("button");
        deleteColumnButton.innerHTML = "Delete column";
        deleteColumnButton.addEventListener("click", this.deleteColumnButtonClick);
        this.dom.appendChild(deleteColumnButton);

        const addColumnHeader = document.createElement("button");
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
        this.dom.appendChild(deleteRowHeader);

        
        table.addEventListener("focus", () => {
            deleteTableButton.classList.remove("hidden");
            table.classList.remove("not-focused");
            table.classList.add("focused");
          });
          
          table.addEventListener("blur", async () => {
            // Add little delay to allow button to be clicked
            await new Promise((resolve) => setTimeout(resolve, 200));
            deleteTableButton.classList.add("hidden");
            table.classList.add("not-focused");
            table.classList.remove("focused");
          });
    }

    addRowButtonClick = () => {
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const newRow = document.createElement("tr");
        newRow.setAttribute("contenteditable", true);
      
        for (let i = 0; i < rows[0].children.length; i++) {
          const td = document.createElement("td");
          td.setAttribute("contenteditable", true);
          td.innerHTML = "<p> </p>";
          newRow.appendChild(td);
        }
      
        tbody.appendChild(newRow);
      }

    deleteRowButtonClick = () => {
        // When it remains only one row, delete the table
        if (this.dom.querySelector("tbody").querySelectorAll("tr").length === 2) {
            this.deleteTable();
            return;
        }
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const lastRow = rows[rows.length - 1];
        lastRow.remove();
    }

    addColumnButtonClick = () => {
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const headerRow = rows[0];
        const newHeaderCell = document.createElement("th");
        newHeaderCell.setAttribute("contenteditable", true);
        newHeaderCell.innerHTML = `Header ${headerRow.children.length + 1}`;
        headerRow.appendChild(newHeaderCell);

        for (let i = 1; i < rows.length; i++) {
            const td = document.createElement("td");
            td.setAttribute("contenteditable", true);
            td.innerHTML = "<p> </p>";
            rows[i].appendChild(td);
        }
    }

    deleteColumnButtonClick = () => {
        // When it remains only one column, delete the table
        if (this.dom.querySelector("table").querySelector("tbody").querySelector("tr").children.length === 1) {
            this.deleteTable();
            return;
        }
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
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
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const firstRow = rows[0];
        const newRow = document.createElement("tr");
        newRow.setAttribute("contenteditable", true);
        for (let i = 0; i < firstRow.children.length; i++) {
            const th = document.createElement("th");
            th.setAttribute("contenteditable", true);
            th.innerHTML = `Header ${i + 1}`;
            newRow.appendChild(th);
        }
        tbody.insertBefore(newRow, firstRow);
        this.columnHeader = true;
    }

    deleteColumnHeaderButtonClick = () => {
        // Delete header only when it exists
        if (!this.columnHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const headerRow = rows[0];
        this.columnHeader = false;
        headerRow.remove();
    }

    addRowHeaderButtonClick = () => {
        // Add header only when it doesn't exist
        if (this.rowHeader)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        for (let i = 0; i < rows.length; i++) {
            const th = document.createElement("th");
            th.setAttribute("contenteditable", true);
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
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        for (let i = 0; i < rows.length; i++) {
            rows[i].children[0].remove();
        }
        this.rowHeader = false;
    }

    deleteTable = () => {
        const { state, dispatch } = this.view;
        const tr = state.tr;
        dispatch(tr.delete(this.getPos(), this.getPos() + 1));
    }
}
