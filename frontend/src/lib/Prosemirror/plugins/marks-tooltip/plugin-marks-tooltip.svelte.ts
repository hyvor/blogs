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

    private props: {
        view: EditorView,
        show: boolean,
        updateId: number
    } = $state({} as any);

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        this.wrap.className = "pm-tooltip"
        view.dom!.parentNode!.appendChild(this.wrap);

        this.props = {
            view: this.view,
            show: false,
            updateId: 0
        }

        mount(MarksTooltip, {
            target: this.wrap,
            props: this.props
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
            this.props.show = false;
            return
        }

        this.props.show = true;
        this.props.updateId++;

    }

    destroy() {
        this.wrap.remove()
    }

}