import { EditorState, Plugin, NodeSelection, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import type { SvelteComponent } from "svelte";
import { selectParentNode } from "prosemirror-commands";
import NodeMenu from "./NodeMenu.svelte";

export default function nodeMenuPlugin() {
    return new Plugin({
        view(editorView) { return new NodeMenuPlugin(editorView) }
    })
}

export class NodeMenuPlugin implements PluginView {

    public view: EditorView;
    private wrap: HTMLElement;

    private dragButton: SvelteComponent | null = null;

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        view.dom!.parentNode!.appendChild(this.wrap);

        this.createDragButtonComponent();
    }

    createDragButtonComponent() {
        if (this.dragButton) {
            this.dragButton.$destroy();
        }
        this.dragButton = new NodeMenu({
            target: this.wrap,
        });

        this.dragButton.$on('drag', (e: CustomEvent) => {
            //selectParentNode(this.view.state, this.view.dispatch, this.view);
        });

        this.dragButton.$on('delete', (e: CustomEvent) => {
            this.deleteNode();
        });

        this.dragButton.$on('duplicate', (e: CustomEvent) => {
            this.duplicateNode();
        });
    }

    deleteNode() {
        const { state, dispatch } = this.view;
        const { $from } = state.selection as NodeSelection;
        const tr = state.tr.delete($from.before(), $from.after());
        dispatch(tr);
    }

    duplicateNode() {
        
    }

    update(view: EditorView, prevState: EditorState) {
        if (prevState.selection.eq(view.state.selection)) return;
        this.createDragButtonComponent();
    }

    destroy() {
        this.dragButton?.$destroy();
        this.wrap.remove();
    }

} 