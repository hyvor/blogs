import type { EditorView, NodeView } from "prosemirror-view";
import { type Node as ProsemirrorNode } from 'prosemirror-model';
import type { SvelteComponent } from "svelte";
import ButtonEditor from "./ButtonEditor.svelte";

export default class ButtonNodeView implements NodeView {
    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;

    dom: HTMLElement;
    contentDOM: HTMLElement;
    link: HTMLAnchorElement;
    buttonEditorWrap: HTMLDivElement;

    private buttonEditor: SvelteComponent;
    showEditMenu: boolean = false;

    constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;

        this.dom = document.createElement("div");
        this.dom.className = "button-wrap";

        this.link = document.createElement("a");
        this.link.className = "button-link";
        this.link.target = "_blank";
        this.link.href = node.attrs.href || "https://example.com";

        this.contentDOM = document.createElement("div");
        this.contentDOM.className = "content-div";

        this.link.appendChild(this.contentDOM);

        this.buttonEditorWrap = document.createElement("div");
        this.buttonEditorWrap.contentEditable = "false";
        this.buttonEditorWrap.className = "button-editor-wrap";
        this.dom.appendChild(this.buttonEditorWrap);

        this.buttonEditor = new ButtonEditor({
            target: this.buttonEditorWrap,
            props: this.getButtonProps()
        });


        this.buttonEditor.$set({ showEditMenu: this.showEditMenu });

        this.dom.appendChild(this.link);

        this.updateFromAttrs();

        // Add focus and blur event listeners
        this.addFocusHandlers();
    }

    private getButtonProps() {
        return {
            showEditMenu: this.showEditMenu,
            href: this.node.attrs.href,
            align: this.node.attrs.align,
            size: this.node.attrs.size,
            bg: this.node.attrs.bg,
            fg: this.node.attrs.fg,
            changeAttr: this.changeAttr.bind(this),
            deleteNode: this.deleteNode.bind(this)
        };
    }

    private addFocusHandlers() {
        this.dom.addEventListener("click", this.handleFocus.bind(this), true);
        this.dom.addEventListener("blur", this.handleBlur.bind(this), true);
    }

    private handleFocus() {
        console.log("Button node focused");
        this.showEditMenu = true;
        this.buttonEditor.$set({ showEditMenu: this.showEditMenu });
    }

    private handleBlur() {
        console.log("Button node lost focus");
        //this.showEditMenu = false;
        this.buttonEditor.$set({ showEditMenu: this.showEditMenu });
    }

    changeSize() {
        let padding = "";
        if (this.node.attrs.size === "small") {
            padding = "10px 10px 10px 10px";
        }
        if (this.node.attrs.size === "medium") {
            padding = "20px 20px 20px 20px";
        }
        if (this.node.attrs.size === "large") {
            padding = "30px 30px 30px 30px";
        }
        this.link.style.padding = padding;
    }

    changeAlign() {
        let align = "";
        if (this.node.attrs.align === "left") {
            align = "start";
        }
        if (this.node.attrs.align === "center") {
            align = "center";
        }
        if (this.node.attrs.align === "right") {
            align = "end";
        }
        this.dom.style.textAlign = align;
    }

    deleteNode() {
        const pos = this.getPos();
        if (pos !== undefined) {
            const tr = this.view.state.tr;
            tr.delete(pos, pos + this.node.nodeSize);
            this.view.dispatch(tr);
        }
    }

    updateFromAttrs() {
        this.link.href = this.node.attrs.href || "https://example.com";
        this.link.style.textAlign = this.node.attrs.align;
        this.link.style.backgroundColor = this.node.attrs.bg;
        this.link.style.color = this.node.attrs.fg;
        this.buttonEditor.$set(this.getButtonProps());
        this.changeSize();
        this.changeAlign();
    }

    update(node: ProsemirrorNode) {
        if (node.type.name === "button") {
            this.node = node;
            this.updateFromAttrs();
            // Delete the node if it's empty
            if (node.content.size === 0) {
                const pos = this.getPos();
                if (pos !== undefined) {
                    const tr = this.view.state.tr;
                    tr.delete(pos, pos + node.nodeSize);
                    this.view.dispatch(tr);
                }
                return true;
            }

            return true;
        }

        return false;
    }

    ignoreMutation(mutation: MutationRecord) {
        if (mutation.target === this.contentDOM) {
            return false;
        }
        return true;
    }

    changeAttr(name: string, value: string) {
        const attrs = { ...this.node.attrs, [name]: value };
        const pos = this.getPos();

        if (pos === undefined) return;

        this.view.dispatch(
            this.view.state.tr.setNodeMarkup(pos, undefined, attrs)
        );
    }
}
