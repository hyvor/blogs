import { EditorState, Plugin, type PluginView } from "prosemirror-state";
import schema from "../../../../../../../lib/prosemirror/schema";
import type { EditorView } from "prosemirror-view";
import type { SvelteComponent } from "svelte";
import Slash from "./Slash.svelte";
import { findOptions } from "./options";

export default function slashPlugin() {
    return new Plugin({
        view(editorView) {
            return new SlashPlugin(editorView);
        },
    });
}

class SlashPlugin implements PluginView {

    private view: EditorView;
    private wrap: HTMLDivElement;
    private component: SvelteComponent;

    private isShow = false;

    constructor(view: EditorView) {
        this.view = view;
        
        this.wrap = document.createElement("div");
        this.wrap.id = "pm-slash-view";
        view.dom!.parentNode!.appendChild(this.wrap);

        this.component = new Slash({
            target: this.wrap,
            props: {
                view: this.view,
                show: false
            }
        });
    }

    private show() {
        if (!this.isShow) {
            this.component.$set({ show: true });
            this.isShow = true;
        }
    }
    private hide() {
        if (this.isShow) {
            this.component.$set({ show: false });
            this.isShow = false;
        }
    }

    update(view: EditorView, prevState: EditorState) {

        let { selection } = view.state;

        if (prevState && prevState.doc.eq(view.state.doc))
            return;

        if (selection.from !== selection.to) return this.hide();

        let { $from } = selection;

        const parent = $from.parent;

        if (!parent || parent.type.name !== "paragraph") {
            return this.hide();
        }

        const text = parent.firstChild?.text;
        if (!text) return this.hide();

        const match = text.match(/^\/(.*)/);
        if (!match) return this.hide();

        const options = findOptions(match[1] || '');
        if (!options.length) return this.hide();

        if (text === '/') {
            this.show();
        }

        this.component.$set({
            options
        });

    }

    destroy() {
        this.wrap.remove();
    }
}