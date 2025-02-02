import type { EditorView, NodeView } from "prosemirror-view";
import { mount } from "svelte";
import Toc from "./Toc.svelte";
import type { Node } from "prosemirror-model";

export default class TocView implements NodeView {

    private node: Node;
    private view: EditorView;
    private getPos: () => number | undefined;

    public dom: HTMLDivElement;

    private levels : undefined|number[] = $state(undefined)

    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement('div');
        this.dom.className = 'toc-wrap';

        this.levels = node.attrs.levels;

        mount(Toc, {
            target: this.dom,
            props: {
                getPos: this.getPos,
                view: this.view,
                levels: this.levels,
            }
        });

    }   

    stopEvent() {
        return true;
    }

    update(node: Node) {
        if (node.type.name === 'toc') {
            this.levels = node.attrs.levels;
            return true;
        }
        return false;
    }

    /* ignoreMutation() {
        return true;
    } */

} 