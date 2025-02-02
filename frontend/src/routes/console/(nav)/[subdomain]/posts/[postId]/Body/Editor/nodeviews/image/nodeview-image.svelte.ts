import type { Node } from "prosemirror-model";
import type { EditorView, NodeView } from "prosemirror-view";
import ImageNodeview from "./ImageNodeview.svelte";
import { mount } from "svelte";

export default class ImageView implements NodeView {

    private node: Node;
    private view: EditorView;
    private getPos: () => number | undefined;

    public dom: HTMLDivElement;

    private props: {
        view: EditorView;
        getPos: () => number | undefined;
        src: string;
        alt: string;
        width: number|null;
        height: number|null;
    } = $state({} as any);


    constructor(node: Node, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement('div');
        this.dom.className = 'image-wrap';

        this.setPropsFromNode(node);

        this.props.view = this.view;
        this.props.getPos = this.getPos;
        this.setPropsFromNode(node);

        mount(ImageNodeview, {
            target: this.dom,
            props: this.props
        });

    }   

    private setPropsFromNode(node: Node) {
        this.props.src = node.attrs.src;
        this.props.alt = node.attrs.alt;
        this.props.width = node.attrs.width;
        this.props.height = node.attrs.height;
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