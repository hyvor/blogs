import { EditorState, NodeSelection, Plugin, type PluginView } from "prosemirror-state"
import type { EditorView } from "prosemirror-view";
import MarksTooltip from "./MarksTooltip.svelte";
import type { SvelteComponent, mount } from "svelte";


export default function marksTooltipPlugin() {
    return new Plugin({
        view(editorView) { return new MarksTooltipPlugin(editorView) }
    })
}

class MarksTooltipPlugin implements PluginView {

    view: EditorView;
    wrap: HTMLElement;
    tooltip: SvelteComponent;

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        this.wrap.className = "pm-tooltip"
        view.dom!.parentNode!.appendChild(this.wrap);

        this.tooltip = mount(MarksTooltip, {
                    target: this.wrap,
                    props: this.getProps()
                });

    }

    private getProps(show = false) {
        return {
            view: this.view,
            show
        }
    }

    private hide() {
        this.tooltip.$set(this.getProps(false))
    }

    private show() {
        this.tooltip.$set(this.getProps(true))
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
            this.hide();
            return
        }

        this.show();

    }

    destroy() {
        this.wrap.remove()
    }

}