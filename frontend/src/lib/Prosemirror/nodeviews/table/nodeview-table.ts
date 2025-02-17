import type { Node } from "prosemirror-model";
import type { EditorView, NodeView } from "prosemirror-view";
import TableTop from "./TableTop.svelte";
import { updateColumnsOnResize } from "prosemirror-tables";
import { mount } from "svelte";


export default class TableNodeView implements NodeView {

    private node: Node;
    private view: EditorView;

    public dom: HTMLDivElement;
    public contentDOM: HTMLDivElement;

    private top: HTMLDivElement;
    private middle: HTMLDivElement;
    private table: HTMLTableElement;
    private colgroup: HTMLTableColElement;


    private readonly MIN_COL_WIDTH = 20;

    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {

        this.node = node;
        this.view = view;

        this.dom = document.createElement("div");
        this.dom.classList.add("table-wrap");

        this.top = this.dom.appendChild(document.createElement("div"));
        this.top.className = "table-top";
        this.top.contentEditable = "false";
        
        mount(TableTop, {target: this.top});
        // this.createRowMenuComponent();

        this.middle = this.dom.appendChild(document.createElement("div"));
        this.middle.className = "table-middle";

        this.table = this.middle.appendChild(document.createElement("table"));
        this.colgroup = this.table.appendChild(document.createElement("colgroup"));
        this.contentDOM = this.table.appendChild(document.createElement("tbody"));

        updateColumnsOnResize(node, this.colgroup, this.table, this.MIN_COL_WIDTH);

    }

    update(node: Node) {
        if (node.type.name === 'table') {
            updateColumnsOnResize(node, this.colgroup, this.table, this.MIN_COL_WIDTH);
            return true;
        }
        return false
    }

    stopEvent() {
        return false;
    }

    ignoreMutation() {
        return true;
    }

    destroy() {
        this.dom.remove();
    }

}