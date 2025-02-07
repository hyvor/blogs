import type { EditorView, NodeView, ViewMutationRecord } from "prosemirror-view";
import { type Node as ProsemirrorNode } from 'prosemirror-model';
import CalloutColors from "./CalloutColors.svelte";
import { mount } from "svelte";

export class CalloutNodeView implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    emoji: HTMLSpanElement;
    colorPickersWrap: HTMLDivElement;

    props: {
        bg: string,
        fg: string,
        changeAttr: (name: string, value: string) => void
    } = $state({} as any)

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

        emoji.onclick = function(e) {
            // TODO
        }
        emoji.onmousedown = function(e) {
            //
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
        
        // picker.on('emoji', selection => {
        //     this.changeAttr('emoji', selection.emoji)
        //     blurFocus()
        // });
        // picker.on("hidden", () => {
        //     blurFocus();
        // });
        
        this.emoji = emoji;
        
        // color pickers
        this.colorPickersWrap = document.createElement("div");
        this.colorPickersWrap.contentEditable = "false";
        this.colorPickersWrap.className = "color-pickers-wrap";
        this.dom.appendChild(this.colorPickersWrap)

        this.props = {
            bg: this.node.attrs.bg,
            fg: this.node.attrs.fg,
            changeAttr: this.changeAttr.bind(this)
        }

        mount(CalloutColors, {
            target: this.colorPickersWrap,
            props: this.props
        })
        
        this.updateFromAttrs();
    }

    ignoreMutation(mutation: ViewMutationRecord) {
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

        this.props.bg = bg;
        this.props.fg = fg;
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