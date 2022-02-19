import { Node } from "prosemirror-model";
import {Plugin, TextSelection} from "prosemirror-state"
import {toggleMark, setBlockType, wrapIn} from "./commands"

export default function navigatorPlugin() {
    return new Plugin({
        view(editorView) { return new NavigatorPlugin(editorView) }
    })
}

class NavigatorPlugin {
    constructor(view) {
        this.items = [];
        this.view = view;

        this.navigator = document.createElement("div")
        this.navigator.className = "pm-navigator"
        document.getElementById("pm-navigator-wrap").appendChild(this.navigator)

    }
  
    update(view, lastState) {
        let state = view.state

        if (
            lastState && 
            lastState.doc.eq(state.doc) &&
            lastState.selection.eq(state.selection)
            ) return

        if (!state.selection.empty) {
            return;
        }

        this.navigator.innerHTML = "";
        const {$from: {path}} = state.selection

        path.forEach(node => {
            if (node instanceof Node && node.type.name !== 'doc') {
                var item = document.createElement("span");
                item.className = "node-item";
                item.innerHTML = node.type.name;
                this.navigator.appendChild(item);
            }
        });

    }
  
    destroy() { this.navigator.remove() }
}