import { NodeSelection, TextSelection } from "prosemirror-state";
import schema from "./schema";

export default class Table {
  
    constructor(node, view, getPos) {
        console.log('Table constructor');
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("table")
        this.renderNode(node);
    }

    renderNode(node) {
        console.log(node);
        this.dom.innerHTML = "";

        const table = document.createElement("table");
        const tbody = document.createElement("tbody");

        for (let i = 0; i < 2; i++) {
            const tr = document.createElement("tr");

            for (let j = 0; j < 2; j++) {
                const td = document.createElement("td");
                td.innerHTML = "Cell";
                tr.appendChild(td);
            }

            tbody.appendChild(tr);
        }

        table.appendChild(tbody);

        this.dom.appendChild(table);

        this.dom.addEventListener("click", this.selectNode);
    }
}
