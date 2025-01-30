import type { Node } from "prosemirror-model";
import type { EditorView, NodeView } from "prosemirror-view";
import ImageNodeview from "./ImageNodeview.svelte";
import type { SvelteComponent, mount } from "svelte";

export default class ImageView implements NodeView {

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
        this.dom.className = 'image-wrap';

        this.component = mount(ImageNodeview, {
                    target: this.dom,
                    props: this.getPropsFromNode(node),
                });

    }   

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

    update(node: Node) {
        if (node.type.name === 'image') {
            this.component.$set(this.getPropsFromNode(node));
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