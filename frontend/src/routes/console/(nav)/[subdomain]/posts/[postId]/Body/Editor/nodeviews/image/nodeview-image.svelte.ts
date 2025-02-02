import type { Node } from "prosemirror-model";
import type { EditorView, NodeView } from "prosemirror-view";
import ImageNodeview from "./ImageNodeview.svelte";
import { mount } from "svelte";

export default class ImageView implements NodeView {

    private node: Node;
    private view: EditorView;
    private getPos: () => number | undefined;

    public dom: HTMLDivElement;

    private src = $state('');
    private alt = $state('');
    private width: null|number = $state(null);
    private height: null|number = $state(null);


    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement('div');
        this.dom.className = 'image-wrap';

        this.setPropsFromNode(node);

        mount(ImageNodeview, {
            target: this.dom,
            props: {
                view: this.view,
                getPos: this.getPos,
                src: this.src,
                alt: this.alt,
                width: this.width,
                height: this.height,
            }
        });

    }   

    private setPropsFromNode(node: Node) {
        this.src = node.attrs.src;
        this.alt = node.attrs.alt;
        this.width = node.attrs.width;
        this.height = node.attrs.height;
    }

    update(node: Node) {
        if (node.type.name === 'image') {
            this.setPropsFromNode(node);
            return true;
        }
        return false;
    }


    stopEvent(e: Event) {
        if (e.target instanceof HTMLElement && e.target.closest('.image-node-wrap .top')) {
            return true;
        }
        return false;
    }

} 