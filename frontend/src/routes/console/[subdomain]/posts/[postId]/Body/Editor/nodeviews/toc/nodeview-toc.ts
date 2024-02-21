import type { EditorView, NodeView } from "prosemirror-view";
import type { SvelteComponent } from "svelte";
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

        this.component = new Toc({
            target: this.dom,
            props: {
                view: this.view
            }
        });

    }   

    stopEvent() {
        return true;
    }

    /* ignoreMutation() {
        return true;
    } */

    private getPropsFromNode(node: Node) {
        return {
            src: node.attrs.src,
            alt: node.attrs.alt,
            width: node.attrs.width,
            height: node.attrs.height,
            getPos: this.getPos,
            view: this.view,
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