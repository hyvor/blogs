import { EditorState, Plugin, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import  { mount } from "svelte";
import Slash from "./Slash.svelte";
import { findOptions, type SlashOption } from "./options";

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
    
    
    private props: {
        view: EditorView;
        show: boolean;
        options: SlashOption[]|undefined;
    } = $state({} as any);

    constructor(view: EditorView) {
        this.view = view;
        
        this.wrap = document.createElement("div");
        this.wrap.id = "pm-slash-view";
        view.dom!.parentNode!.appendChild(this.wrap);

        this.props = {
            view: this.view,
            show: false,
            options: undefined,
        }

        mount(Slash, {
            target: this.wrap,
            props: this.props
        });
    }

    private show() {
        this.props.show = true;
    }
    private hide() {
        this.props.show = false;
    }

    update(view: EditorView, prevState: EditorState) {

        let { selection } = view.state;

        if (prevState && prevState.doc.eq(view.state.doc))
            return;

        if (selection.from !== selection.to) return this.hide();

        const parent = selection.$from.parent;

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

        this.props.options = options;

    }

    destroy() {
        this.wrap.remove();
    }
}