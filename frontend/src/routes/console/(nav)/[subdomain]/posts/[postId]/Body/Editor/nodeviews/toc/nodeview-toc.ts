import type { EditorView, NodeView } from "prosemirror-view";
import type { SvelteComponent, mount } from "svelte";
import Toc from "./Toc.svelte";
import type { Node } from "prosemirror-model";

export default class TocView implements NodeView {

    private node: Node;
    private view: EditorView;
    private getPos: () => number | undefined;

    public dom: HTMLDivElement;

    private component: SvelteComponent;

    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement('div');
        this.dom.className = 'toc-wrap';

        this.component = mount(Toc, {
                    target: this.dom,
                    props: this.getPropsFromNode(node)
                });

    }   

    stopEvent() {
        return true;
    }

    update(node: Node) {
        if (node.type.name === 'toc') {
            this.component.$set(this.getPropsFromNode(node));
            return true;
        }
        return false;
    }

    /* ignoreMutation() {
        return true;
    } */

    private getPropsFromNode(node: Node) {
        return {
            getPos: this.getPos,
            view: this.view,
            levels: node.attrs.levels,
        }
    }

    /* update(node: Node) {
        console.log('update called');
        return false;
        if (node.type.name === 'image') {
            this.component.$set(this.getPropsFromNode(node));
            return true;
        }
        return false;
    } */

} 