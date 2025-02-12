import { EditorState, Plugin, type PluginView } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import { mount } from "svelte";
import NodeMenu from "./NodeMenu.svelte";
import { nodeMenuPos, nodeMenuUpdateId, resolveNodeMenuPos } from "./node-menu";

function unsetNear() {
    nodeMenuPos.set(null);
}

export default function nodeMenuPlugin() {
    return new Plugin({
        props: {
            handleDOMEvents: {
                mouseover(view, event) {
                    return;
                    const pos = view.posAtCoords({ left: event.clientX, top: event.clientY });
                    if (!pos) {
                        return unsetNear();
                    }

                    // const dom = view.nodeDOM(pos.pos);

                    // if (
                    //     dom === null ||
                    //     dom.nodeType !== Node.ELEMENT_NODE
                    // )
                    // {
                    //     return unsetNear();
                    // }

                    console.log("pos", pos.pos)
                    nodeMenuPos.set(pos.pos);
                }
            }
        },
        view(editorView) { return new NodeMenuPlugin(editorView) }
    })
}

export class NodeMenuPlugin implements PluginView {
    public view: EditorView;
    private wrap: HTMLElement;

    constructor(view: EditorView) {
        this.view = view;

        this.wrap = document.createElement("div")
        view.dom!.parentNode!.appendChild(this.wrap);

        mount(NodeMenu, {
            target: this.wrap,
            props: {
                view: this.view,
            }
        });
    }
    

    update(view: EditorView, prevState: EditorState) {
        if (prevState.selection.eq(view.state.selection)) return;

        const correctPos = resolveNodeMenuPos(view.state.selection.$from);

        nodeMenuPos.set(correctPos);
    }


    destroy() {
        this.wrap.remove();
    }

} 