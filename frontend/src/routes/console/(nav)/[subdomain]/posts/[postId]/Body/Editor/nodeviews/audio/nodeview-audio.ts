import type { Node } from "prosemirror-model";
import type { EditorView, NodeView } from "prosemirror-view";
import AudioNodeView from "./AudioNodeView.svelte";
import type { SvelteComponent, mount } from "svelte";

export default class AudioView implements NodeView {

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

        this.component = mount(AudioNodeView, {
                    target: this.dom,
                    props: this.getPropsFromNode(node),
                });

    }   

    private getPropsFromNode(node: Node) {
        return {
            src: node.attrs.src,
            getPos: this.getPos,
            view: this.view,
        }
    }

    update(node: Node) {
        if (node.type.name === 'audio') {
            this.component.$set(this.getPropsFromNode(node));
            return true;
        }
        return false;
    }


    stopEvent() {
        return false;
    }

} 