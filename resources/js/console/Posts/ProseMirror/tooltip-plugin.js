import {Plugin, TextSelection, NodeSelection} from "prosemirror-state"
import {toggleMark, setBlockType, wrapIn} from "./commands"

const icons = {
    bold: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type-bold" viewBox="0 0 16 16">
        <path d="M8.21 13c2.106 0 3.412-1.087 3.412-2.823 0-1.306-.984-2.283-2.324-2.386v-.055a2.176 2.176 0 0 0 1.852-2.14c0-1.51-1.162-2.46-3.014-2.46H3.843V13H8.21zM5.908 4.674h1.696c.963 0 1.517.451 1.517 1.244 0 .834-.629 1.32-1.73 1.32H5.908V4.673zm0 6.788V8.598h1.73c1.217 0 1.88.492 1.88 1.415 0 .943-.643 1.449-1.832 1.449H5.907z"/>
    </svg>`,
    italic: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type-italic" viewBox="0 0 16 16">
        <path d="M7.991 11.674 9.53 4.455c.123-.595.246-.71 1.347-.807l.11-.52H7.211l-.11.52c1.06.096 1.128.212 1.005.807L6.57 11.674c-.123.595-.246.71-1.346.806l-.11.52h3.774l.11-.52c-1.06-.095-1.129-.211-1.006-.806z"/>
    </svg>`,
    code: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-code" viewBox="0 0 16 16">
        <path d="M5.854 4.854a.5.5 0 1 0-.708-.708l-3.5 3.5a.5.5 0 0 0 0 .708l3.5 3.5a.5.5 0 0 0 .708-.708L2.707 8l3.147-3.146zm4.292 0a.5.5 0 0 1 .708-.708l3.5 3.5a.5.5 0 0 1 0 .708l-3.5 3.5a.5.5 0 0 1-.708-.708L13.293 8l-3.147-3.146z"/>
    </svg>`,
    strike: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-type-strikethrough" viewBox="0 0 16 16">
        <path d="M6.333 5.686c0 .31.083.581.27.814H5.166a2.776 2.776 0 0 1-.099-.76c0-1.627 1.436-2.768 3.48-2.768 1.969 0 3.39 1.175 3.445 2.85h-1.23c-.11-1.08-.964-1.743-2.25-1.743-1.23 0-2.18.602-2.18 1.607zm2.194 7.478c-2.153 0-3.589-1.107-3.705-2.81h1.23c.144 1.06 1.129 1.703 2.544 1.703 1.34 0 2.31-.705 2.31-1.675 0-.827-.547-1.374-1.914-1.675L8.046 8.5H1v-1h14v1h-3.504c.468.437.675.994.675 1.697 0 1.826-1.436 2.967-3.644 2.967z"/>
    </svg>`,
    link: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-link-45deg" viewBox="0 0 16 16">
        <path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/>
        <path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/>
    </svg>`
}

export default function tooltipPlugin() {
    return new Plugin({
        view(editorView) { return new MarksTooltip(editorView) }
    })
}

function isMarkActive(state, type) {
    let {from, $from, to, empty} = state.selection
    if (empty) return type.isInSet(state.storedMarks || $from.marks())
    else return state.doc.rangeHasMark(from, to, type)
}

class MarksTooltip {
    constructor(view) {
        this.items = [];
        this.view = view;

        this.tooltip = document.createElement("div")
        this.tooltip.className = "pm-tooltip"
        view.dom.parentNode.appendChild(this.tooltip)

        // this.addLinkItem();
        const schema = view.state.schema;
        this.addItem(schema.marks.link, icons.link, true);
        this.addItem(schema.marks.strong, icons.bold);
        this.addItem(schema.marks.em, icons.italic);
        this.addItem(schema.marks.code, icons.code)
        this.addItem(schema.marks.strike, icons.strike)

        this.update(view, null)
    }

    addItem(type, html, isLink = false) {
        var dom = document.createElement("span");
        dom.innerHTML = html;
        this.tooltip.appendChild(dom);

        var selfx = this;
        const view = this.view;

        dom.addEventListener("click", function() {
            if (isLink) {

                const isActive = isMarkActive(view.state, type);
                if (isActive) {
                    toggleMark(type)(view.state, view.dispatch, view);
                } else {
                    var input = document.createElement("input");
                    input.type = "text";
                    input.autocomplete = false;
                    input.placeholder = "Enter a link..."
                    selfx.tooltip.appendChild(input);
                    selfx.tooltip.classList.add("linking")

                    var closeIcon = document.createElement("a");
                    closeIcon.innerHTML = "&times;";
                    closeIcon.classList.add("input-close")
                    closeIcon.onclick = closeLinkInput;
                    selfx.tooltip.appendChild(closeIcon);

                    function closeLinkInput(focusAtEnd = false) {
                        selfx.tooltip.classList.remove("linking");
                        selfx.tooltip.removeChild(input);
                        selfx.tooltip.removeChild(closeIcon)

                        if (focusAtEnd) {
                            const tr = selfx.view.state.tr;
                            const selection = TextSelection.create(tr.doc, selfx.view.state.selection.to);
                            selfx.view.dispatch(tr.setSelection(selection));
                            selfx.view.focus();
                        }
                    }

                    input.addEventListener("keydown", (e) => {
                        if (e.key === 'Enter') {
                            input.value ?
                                toggleMark(type, {href: input.value})(view.state, view.dispatch, view) :
                                closeLinkInput();
                            closeLinkInput(true);
                            e.preventDefault(); // otherwise makes a new paragraph
                        } else if (e.key === 'Escape') {
                            closeLinkInput();
                            e.stopPropagation();
                        }
                    })
                    input.focus()
                    return
                }

            } else {
                toggleMark(type)(view.state, view.dispatch, view);
            }

            view.focus();
        });

        this.items.push({
            type,
            dom
        })
    }
  
    update(view, lastState) {
        let state = view.state

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
            this.tooltip.style.display = "none"
            return
        }

        this.items.forEach(({type, dom}) => {
            let active = isMarkActive(this.view.state, type);
            if (active) {
                dom.classList.add("active")
            } else {
                dom.classList.remove("active")
            }
        })

        // Otherwise, reposition it and update its content
        this.tooltip.style.display = ""
        const {from, to} = state.selection

        /**
         * Find the maximum and minimum left points of the current selection
         * Then, the tooltip is placed in the middle of them
         */
        let startLeft = Infinity, endLeft = 0;
        for (let i = from; i <= to; i++) {
            startLeft = Math.min(startLeft, view.coordsAtPos(i).left)
            endLeft = Math.max(endLeft, view.coordsAtPos(i).left)
        }

        // The box in which the tooltip is positioned, to use as base
        let box = this.tooltip.offsetParent.getBoundingClientRect()
        // Find a center-ish x position from the selection endpoints (when
        // crossing lines, end may be more to the left)

        let left = (endLeft - startLeft) / 2;
        this.tooltip.style.left = 
            (startLeft - box.left + left - (this.tooltip.getBoundingClientRect().width / 2)) + "px"
        this.tooltip.style.bottom = (box.bottom - view.coordsAtPos(from).top) + "px"
    }
  
    destroy() { this.tooltip.remove() }
}
