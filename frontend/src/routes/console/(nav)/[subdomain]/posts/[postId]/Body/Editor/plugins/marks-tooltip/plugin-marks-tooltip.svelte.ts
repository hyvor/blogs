import { EditorState, NodeSelection, Plugin, type PluginView } from "prosemirror-state"
import type { EditorView } from "prosemirror-view";
import MarksTooltip from "./MarksTooltip.svelte";
import  { mount } from "svelte";


export default function marksTooltipPlugin() {
    return new Plugin({
        view(editorView) { return new MarksTooltipPlugin(editorView) }
    })
}

class MarksTooltipPlugin implements PluginView {

    view: EditorView;
    wrap: HTMLElement;

    private show = $state(false);

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        this.wrap.className = "pm-tooltip"
        view.dom!.parentNode!.appendChild(this.wrap);

        mount(MarksTooltip, {
            target: this.wrap,
            props: {
                view: this.view,
                show: this.show
            }
        });

    }

    update(view: EditorView, lastState: EditorState): void {

        const state = view.state

        if (
            lastState && 
            lastState.doc.eq(state.doc) &&
            lastState.selection.eq(state.selection)
        ) return

        if (
            state.selection.empty || 
            !view.editable ||
            state.doc.cut(state.selection.from, state.selection.to).textContent === "" ||
            state.selection instanceof NodeSelection
        ) {
            this.show = false;
            return
        }

        this.show = true;

    }

    destroy() {
        this.wrap.remove()
    }

}