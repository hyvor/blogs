import { EditorState, Plugin, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import type { SvelteComponent, mount } from "svelte";
import TableRowMenu from "./TableRowMenu.svelte";
import TableColumnMenu from "./TableColumnMenu.svelte";

export default function tableMenuPlugin() {
    return new Plugin({
        view(editorView) { return new PluginTableMenu(editorView) }
    })
}

export class PluginTableMenu implements PluginView {

    public view: EditorView;
    private wrap: HTMLElement;

    private rowMenu: SvelteComponent | null = null;
    private columnMenu: SvelteComponent | null = null;

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        view.dom!.parentNode!.appendChild(this.wrap);

        this.createRowMenuComponent();
        this.createColumnMenuComponent();

    }

    private createRowMenuComponent() {
        if (this.rowMenu) {
            this.rowMenu.$destroy();
        }
        this.rowMenu = mount(TableRowMenu, {
                    target: this.wrap
                });
    }

    private createColumnMenuComponent() {
        if (this.columnMenu) {
            this.columnMenu.$destroy();
        }
        this.columnMenu = mount(TableColumnMenu, {
                    target: this.wrap
                });
    }

    update(view: EditorView, prevState: EditorState) {
        if (prevState.selection.eq(view.state.selection)) return;
        this.createRowMenuComponent();
        this.createColumnMenuComponent();
    }

    destroy() {
        this.rowMenu?.$destroy();
        this.wrap.remove();
    }

} 