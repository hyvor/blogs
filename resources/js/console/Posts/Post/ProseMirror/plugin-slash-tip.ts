import {EditorState, Plugin, PluginView} from 'prosemirror-state';
import {EditorView} from "prosemirror-view";
import {isEditorRtl} from "./rtl";

export default function slashTipPlugin() {
    return new Plugin({
        view: (view) => new SlashTipPlugin(view)
    })
}

class SlashTipPlugin implements PluginView {

    element: HTMLDivElement;

    constructor(view: EditorView) {
        this.element = document.createElement('div');
        this.element.innerHTML = 'Type / to insert a block';
        this.element.className = 'pm-slash-tip';
        view.dom.parentElement?.appendChild(this.element);
        view.dom
            .parentElement!
            .parentElement!
            .parentElement!
            .addEventListener('scroll', () => this.update(view, view.state));
        this.hide();
    }

    hide() {
        if (this.element.style.display !== 'none') {
            this.element.style.display = 'none';
        }
    }

    update(view: EditorView, prevState: EditorState) {

        const {state} = view;
        const {selection} = state;


        if (!selection.empty) {
            return this.hide();
        }

        const parent = selection.$from.parent;
        const grandParent = selection.$from.node(-1);
        if (parent.type.name !== 'paragraph' || grandParent.type.name == 'table_cell')
            return this.hide();

        if (parent.content.size > 0)
            return this.hide();

        this.element.style.display = 'block';

        const coords = view.coordsAtPos(selection.$from.pos);
        const posTop = coords.top;

        const wrapPos = this.element.offsetParent!.getBoundingClientRect()
        const viewPos = view.dom.getBoundingClientRect()

        this.element.style.top = (posTop - wrapPos.top) + "px";

        const isRtl = isEditorRtl();

        if (isRtl) {
            this.element.style.left = 25 + "px";
            return;
        }

        this.element.style.right = (viewPos.left - wrapPos.left) + "px";

    }

}