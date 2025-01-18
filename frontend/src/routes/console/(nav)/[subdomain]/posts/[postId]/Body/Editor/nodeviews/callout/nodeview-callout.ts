import type { EditorView, NodeView } from "prosemirror-view";
import { type Node as ProsemirrorNode } from 'prosemirror-model';
import { EmojiButton } from '@joeattardi/emoji-button';
import CalloutColors from "./CalloutColors.svelte";
import type { SvelteComponent } from "svelte";

export class CalloutNodeView implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    emoji: HTMLSpanElement;
    colorPickersWrap: HTMLDivElement;

    private colorsComponent: SvelteComponent;

    constructor(node: ProsemirrorNode, view: EditorView, getPos: () => number | undefined) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        
        this.dom = document.createElement("aside")

        this.contentDOM = document.createElement("div")
        this.contentDOM.className = "content-div";

        const emoji = document.createElement("span");
        emoji.contentEditable = "false";
        emoji.className = 'emoji-icon'

        this.dom.appendChild(emoji)
        this.dom.appendChild(this.contentDOM)

        const picker = new EmojiButton();
        
        let lastSelection = null;
        emoji.onclick = function(e) {
            picker.togglePicker(emoji);
            lastSelection = view.state.selection;
        }
        emoji.onmousedown = function(e) {
            e.preventDefault();
        }

        /**
         * Focusing doesn't work correctly for unknown reason
         * For now, we just blur the editor
         * 
         * Blur doesn't work correctly too. It focuses the start of the doc before blurring
         * TODO: Fix this
         */
        function blurFocus() {
            view.dom.blur();
            (window as any).getSelection().removeAllRanges()
        }
        
        picker.on('emoji', selection => {
            this.changeAttr('emoji', selection.emoji)
            blurFocus()
        });
        picker.on("hidden", () => {
            blurFocus();
        });
        
        this.emoji = emoji;
        
        // color pickers
        this.colorPickersWrap = document.createElement("div");
        this.colorPickersWrap.contentEditable = "false";
        this.colorPickersWrap.className = "color-pickers-wrap";
        this.dom.appendChild(this.colorPickersWrap)

        this.colorsComponent = new CalloutColors({
            target: this.colorPickersWrap,
            props: this.getColorsProps()
        })
        
        this.updateFromAttrs();
    }

    private getColorsProps() {
        return {
            bg: this.node.attrs.bg,
            fg: this.node.attrs.fg,
            changeAttr: this.changeAttr.bind(this)
        }
    }

    ignoreMutation(mutation: MutationRecord) {
        if (mutation.target === this.contentDOM) {
            return false;
        }
        return true;
    }

    update(node: ProsemirrorNode) {
        
        if (node.type.name === 'callout') {
            this.node = node;
            this.updateFromAttrs()
            return true;
        }

        return false;
        
    }
    
    updateFromAttrs() {
        this.emoji.innerHTML = this.node.attrs.emoji;
        this.changeColors(this.node.attrs.bg, this.node.attrs.fg)
        this.dom.dataset.emoji = this.node.attrs.emoji;
    }
    
    changeColors(bg: string, fg: string) {
        this.dom.style.backgroundColor = bg;
        this.dom.style.color = fg;
        this.colorsComponent.$set(this.getColorsProps())
    }

    changeAttr(name: string, value: string) {
        const attrs = {...this.node.attrs, [name]: value }
        const pos = this.getPos();

        if (pos === undefined)
            return;

        this.view.dispatch(
            this.view.state.tr.setNodeMarkup(
                pos,
                undefined,
                attrs
            )
        )
    }

}