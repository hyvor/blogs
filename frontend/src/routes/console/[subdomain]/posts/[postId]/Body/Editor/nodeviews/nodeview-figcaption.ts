import type { Node } from "prosemirror-model";
import type { NodeView } from "prosemirror-view"

export default class FigcaptionNodeView implements NodeView {

    dom: HTMLElement;
    contentDOM: HTMLElement;

    constructor(node: Node) {
        this.dom = this.contentDOM = document.createElement("figcaption")
        if (node.content.size == 0) this.dom.classList.add("empty")

        this.dom.addEventListener("click", function() {
            
        })
    }

    update(node: Node) {
        if (node.type.name != "figcaption") return false
        if (node.content.size > 0) this.dom.classList.remove("empty")
        else this.dom.classList.add("empty")
        return true
    }

}