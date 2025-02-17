import { EditorState, Plugin, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import { mount, unmount } from "svelte";
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

    private rowMenu: Record<string, any> | null = null;
    private columnMenu: Record<string, any> | null = null;

    private props:{
        updateId: number;
    } = $state({ updateId: 0 });

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        view.dom!.parentNode!.appendChild(this.wrap);

        this.rowMenu = mount(TableRowMenu, {
            target: this.wrap,
            props: this.props
        });

        this.columnMenu = mount(TableColumnMenu, {
            target: this.wrap,
            props: this.props
        });

    }

    update(view: EditorView, prevState: EditorState) {
        if (prevState.selection.eq(view.state.selection)) return;
        this.props.updateId++;
    }

    destroy() {
        this.rowMenu && unmount(this.rowMenu);
        this.columnMenu && unmount(this.columnMenu);
        this.wrap.remove();
    }

} 