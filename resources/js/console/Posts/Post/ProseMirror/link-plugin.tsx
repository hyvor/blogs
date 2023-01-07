import {EditorState, NodeSelection, Plugin, TextSelection} from "prosemirror-state";
import {EditorView} from "prosemirror-view";
import React, {useEffect, useState} from "react";
import {render, unmountComponentAtNode} from "react-dom";
import {BoxArrowUpRight, Pencil, Trash} from "react-bootstrap-icons";
import {Mark, MarkType, ResolvedPos} from "prosemirror-model";
import {createRoot, Root} from "react-dom/client";
import {toggleMark} from "prosemirror-commands";

export default function linkPlugin() {

    return new Plugin({
        view(editorView) { return new LinkPlugin(editorView) }
    })

}

class LinkPlugin {

    private readonly tooltip : HTMLDivElement;

    private reactRoot : null | Root = null;

    constructor(private view: EditorView) {

        this.tooltip = document.createElement("div")
        this.tooltip.className = "pm-link-tooltip"
        view.dom.parentNode!.appendChild(this.tooltip)

        this.hide();

    }

    update(view: EditorView, lastState: EditorState) {

        const state = view.state;

        if (
            lastState &&
            lastState.doc.eq(state.doc) &&
            lastState.selection.eq(state.selection)
        ) return

        if (!state.selection.empty) {
            return this.hide();
        }

        const marks = state.selection.$head.marks();

        const link = marks.find(mark => mark.type.name === "link");

        if (!link) {
            return this.hide();
        }

        return this.show(view, link);

    }

    private show(view: EditorView, linkMark: Mark) {

        this.tooltip.style.display = "block"

        if (this.reactRoot) {
            this.reactRoot.unmount();
        }

        this.reactRoot = createRoot(this.tooltip);
        this.reactRoot.render(<LinkTooltip
            mark={linkMark}
            view={view}
            tooltip={this.tooltip}
        />);

    }

    private hide() {
        this.tooltip.style.display = "none"
    }


}


function LinkTooltip({ mark, view, tooltip } : { mark: Mark, view: EditorView, tooltip: HTMLDivElement }) {

    const link = mark.attrs.href;

    const [editingLink, setEditingLink] = useState(link);
    const [isEditing, setIsEditing] = useState(false)

    useEffect(() => {
        positionTooltip(tooltip, view);
    }, []);

    function handleRemove() {
        const extend = markExtend(view.state.selection.$from, mark);

        view.dispatch(
            view.state.tr.removeMark(
                extend.from,
                extend.to,
                view.state.schema.marks.link
            )
        );
    }


    function handleEdit() {

        setIsEditing(false);

        const extend = markExtend(view.state.selection.$from, mark);

        view.dispatch(
            view.state.tr
                .removeMark(extend.from, extend.to, view.state.schema.marks.link)
                .addMark(extend.from, extend.to, view.state.schema.marks.link.create({ href: editingLink }))
        )

    }


    if (isEditing) {

        return <div>

            <input
                type="text"
                value={editingLink}
                onChange={(e) => setEditingLink(e.target.value)}
                autoFocus={true}
                onKeyUp={e => {
                    if (e.key === "Enter") {
                        handleEdit()
                    }
                    if (e.key === "Escape") {
                        setIsEditing(false)
                    }
                }}
            />

            <span className="edit-closer" onClick={() => setIsEditing(false)}>
                &times;
            </span>



        </div>

    }

    return (
        <div>
            <a href={link} target="_blank">
                {link.substring(0, 60) + (link.length > 60 ? "..." : '')}&nbsp;<BoxArrowUpRight />
            </a>
            <button onClick={() => setIsEditing(true)}><Pencil /></button>
            <button onClick={handleRemove}><Trash /></button>
        </div>
    )

}

function positionTooltip(tooltip: HTMLDivElement, view: EditorView) {

    // position tool tip
    const {from, to} = view.state.selection
    let startLeft = Infinity, endLeft = 0;
    for (let i = from; i <= to; i++) {
        startLeft = Math.min(startLeft, view.coordsAtPos(i).left)
        endLeft = Math.max(endLeft, view.coordsAtPos(i).left)
    }

    // The box in which the tooltip is positioned, to use as base
    let box = tooltip.offsetParent!.getBoundingClientRect()
    // Find a center-ish x position from the selection endpoints (when
    // crossing lines, end may be more to the left)

    let left = (endLeft - startLeft) / 2;
    tooltip.style.left =
        (startLeft - box.left + left - (tooltip.getBoundingClientRect().width / 2)) + "px"
    tooltip.style.bottom = (box.bottom - view.coordsAtPos(from).top) + "px"

}

function markExtend ($start: ResolvedPos, mark: Mark) {
    let startIndex = $start.index()
        , endIndex = $start.indexAfter()
    ;
    while (startIndex > 0 && mark.isInSet($start.parent.child(startIndex - 1).marks)) startIndex--;
    while (
        endIndex < $start.parent.childCount &&
        mark.isInSet($start.parent.child(endIndex).marks)) endIndex++;
    let startPos = $start.start()
        , endPos = startPos
    ;
    for (let i = 0; i < endIndex; i++) {
        let size = $start.parent.child(i).nodeSize;
        if (i < startIndex) startPos += size;
        endPos += size;
    }
    return { from: startPos, to: endPos };
}