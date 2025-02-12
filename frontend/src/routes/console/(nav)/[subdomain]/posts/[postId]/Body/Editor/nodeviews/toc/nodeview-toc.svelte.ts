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

    private props: {
        getPos: () => number | undefined;
        view: EditorView;
        levels: undefined|number[];
    } = $state({} as any)

    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement('div');
        this.dom.className = 'toc-wrap';

        this.props = {
            getPos: this.getPos,
            view: this.view,
            levels: this.levels
        }

        mount(Toc, {
            target: this.dom,
            props: this.props
        });

    }   

    stopEvent() {
        return true;
    }

    update(node: Node) {
        if (node.type.name === 'toc') {
            this.props.levels = node.attrs.levels;
            return true;
        }
        return false;
    }

    /* ignoreMutation() {
        return true;
    } */

} 