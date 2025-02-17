import type { Node } from "prosemirror-model";
import type { NodeView } from "prosemirror-view";
import EmbedNodeview from "./embed/EmbedNodeview.svelte";
import { mount } from "svelte";

export default class BookmarkView implements NodeView {

    dom: HTMLElement;
    node: Node;

    constructor(node: Node) {
        this.node = node;

        this.dom = document.createElement("x-embed");

        const url = node.attrs.url;

        mount(EmbedNodeview, {
            target: this.dom,
            props: {
                url,
                type: 'link'
            }
        })

    }

}
