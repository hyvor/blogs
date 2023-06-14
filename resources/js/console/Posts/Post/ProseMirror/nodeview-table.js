import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";

export default class Table {
  
    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        this.header = true; // Table create with headers

        this.dom = document.createElement("table")
        this.renderNode(node, getPos);
    }

    renderNode(node, pos) {
        this.dom.innerHTML = "";

        const table = document.createElement("table");
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

        this.dom.appendChild(table);

        this.dom.addEventListener("click", this.selectNode);

        const addRowButton = document.createElement("button");
        addRowButton.innerHTML = "Add row";
        addRowButton.addEventListener("click", this.addRowButtonClick);
        this.dom.appendChild(addRowButton);

        const deleteRowButton = document.createElement("button");
        deleteRowButton.innerHTML = "Delete row";
        deleteRowButton.addEventListener("click", this.deleteRowButtonClick);
        this.dom.appendChild(deleteRowButton);

        const deleteTableButton = document.createElement("button");
        deleteTableButton.innerHTML = "Delete table";
        deleteTableButton.addEventListener("click", this.deleteTable);
        this.dom.appendChild(deleteTableButton);

        const addColumnButton = document.createElement("button");
        addColumnButton.innerHTML = "Add column";
        addColumnButton.addEventListener("click", this.addColumnButtonClick);
        this.dom.appendChild(addColumnButton);

        const deleteColumnButton = document.createElement("button");
        deleteColumnButton.innerHTML = "Delete column";
        deleteColumnButton.addEventListener("click", this.deleteColumnButtonClick);
        this.dom.appendChild(deleteColumnButton);

        const deleteHeaderButton = document.createElement("button");
        deleteHeaderButton.innerHTML = "Delete header";
        deleteHeaderButton.addEventListener("click", this.deleteHeaderButtonClick);
        this.dom.appendChild(deleteHeaderButton);
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

    deleteHeaderButtonClick = () => {
        // Delete header only when it exists
        if (!this.header)
            return;
        const table = this.dom.querySelector("table");
        const tbody = table.querySelector("tbody");
        const rows = tbody.querySelectorAll("tr");
        const headerRow = rows[0];
        this.header = false;
        headerRow.remove();
    }

    deleteTable = () => {
        const { state, dispatch } = this.view;
        const tr = state.tr;
        dispatch(tr.delete(this.getPos(), this.getPos() + 1));
    }
}
