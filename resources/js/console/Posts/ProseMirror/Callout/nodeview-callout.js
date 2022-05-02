import { EmojiButton } from '@joeattardi/emoji-button';
import ColorPicker from "./ColorPicker";
import ReactDOM from 'react-dom';

export default class Callout {

    constructor(node, view, getPos) {
        this.node = node;
        this.view = view;
        this.getPos = getPos;
        
        this.dom = document.createElement("aside")

        this.contentDOM = document.createElement("div")
        this.contentDOM.className = "content-div";

        const emoji = document.createElement("span");
        emoji.contentEditable = false;
        emoji.className = 'emoji-icon'

        this.dom.appendChild(emoji)
        this.dom.appendChild(this.contentDOM)

        const picker = new EmojiButton();
        
        let lastSelection = null;
        emoji.onclick = function(e) {
            picker.togglePicker(emoji)
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
            window.getSelection().removeAllRanges()
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
        this.colorPickersWrap.contentEditable = false;
        this.colorPickersWrap.className = "color-pickers-wrap";
        this.dom.appendChild(this.colorPickersWrap);
        
        this.colorPickerBg = this.createColorPicker('bg')
        this.colorPickerFg = this.createColorPicker('fg')
        
        this.updateFromAttrs();
    }

    update(node) {
        
        if (node.type.name === 'callout') {
            this.node = node;
            this.updateFromAttrs()
            return true;
        }
        
    }
    
    updateFromAttrs() {
        this.emoji.innerHTML = this.node.attrs.emoji;
        this.changeColors(this.node.attrs.bg, this.node.attrs.fg)
    }
    
    changeColors(bg, fg) {
        this.dom.style.backgroundColor = bg;
        this.dom.style.color = fg;
        this.colorPickerBg.style.backgroundColor = bg;
        this.colorPickerFg.style.backgroundColor = fg;
    }
    
    createColorPicker(type) {
        
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
            
            ReactDOM.render(
                <ColorPicker
                    color={_self.node.attrs[type]}
                    onChange={color => _self.changeAttr(type, color)}
                    onClose={() => document.body.removeChild(pickerWrap)}
                    preset={preset}
                />,
                pickerWrap
            )
        });
        
        this.colorPickersWrap.appendChild(picker)
        
        return picker
        
    }
    
    changeAttr(name, value) {
        const attrs = {...this.node.attrs, [name]: value }
        this.view.dispatch(
            this.view.state.tr.setNodeMarkup(
                this.getPos(),
                undefined,
                attrs
            )
        )
    }
    
}
