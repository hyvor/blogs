import type { EditorView, NodeView } from "prosemirror-view";
import { type Node as ProsemirrorNode } from 'prosemirror-model';
import { EmojiButton } from '@joeattardi/emoji-button';

export class CalloutNodeView implements NodeView {

    node: ProsemirrorNode;
    view: EditorView;
    getPos: () => number | undefined;

    dom: HTMLElement;
    contentDOM: HTMLElement;

    emoji: HTMLSpanElement;
    colorPickersWrap: HTMLDivElement;
    colorPickerBg: HTMLSpanElement;
    colorPickerFg: HTMLSpanElement;


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
        this.dom.appendChild(this.colorPickersWrap);
        
        this.colorPickerBg = this.createColorPicker('bg')
        this.colorPickerFg = this.createColorPicker('fg')
        
        this.updateFromAttrs();
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
        this.colorPickerBg.style.backgroundColor = bg;
        this.colorPickerFg.style.backgroundColor = fg;
    }
    
    createColorPicker(type: 'bg' | 'fg') {
        
        const picker = document.createElement("span");
        picker.className = 'color-picker';
        
        const _self =  this;
        
        picker.addEventListener('click', function () {
            const pickerWrap = document.createElement("div");
            document.body.appendChild(pickerWrap)

            pickerWrap.style.position = 'fixed';
            const cord = picker.getBoundingClientRect()
            pickerWrap.style.top = (cord.top + 25) + "px";
            pickerWrap.style.left = (cord.left - 200) + "px";
            pickerWrap.style.zIndex = "100000";
            
            const preset = type === 'bg' ? 
                [
                    '#e3e2e080',
                    '#e3e2e0',
                    '#eedfda',
                    '#f9dec9',
                    '#fdecc8',
                    '#daecda',
                    '#d2e4ef',
                    '#e7ddee',
                    '#ffe2dd'
                ] : 
                [
                    '#000', '#fff'
                ];
            
            /* ReactDOM.render(
                <ColorPicker
                    color={_self.node.attrs[type]}
                    onChange={(color: string) => _self.changeAttr(type, color)}
                    onClose={() => document.body.removeChild(pickerWrap)}
                    preset={preset}
                />,
                pickerWrap
            ) */
        });
        
        this.colorPickersWrap.appendChild(picker)
        
        return picker
        
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