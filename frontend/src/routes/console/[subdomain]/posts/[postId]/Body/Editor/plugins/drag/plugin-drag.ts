import { EditorState, Plugin, NodeSelection, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import type { SvelteComponent } from "svelte";
import DragButton from "./DragButton.svelte";
import { selectParentNode } from "prosemirror-commands";

export default function dragPlugin() {
    return new Plugin({
        view(editorView) { return new DragPlugin(editorView) }
    })
}

export class DragPlugin implements PluginView {

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
        this.dragButton = new DragButton({
            target: this.wrap,
        });
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