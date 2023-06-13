import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";

export default class Table {
  
    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("table")
        this.renderNode(node);
    }

    renderNode(node) {
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
                td.innerHTML = "<p></p>";
                tr.appendChild(td);
            }

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);

        this.dom.appendChild(table);

        this.dom.addEventListener("click", this.selectNode);
    }

}
