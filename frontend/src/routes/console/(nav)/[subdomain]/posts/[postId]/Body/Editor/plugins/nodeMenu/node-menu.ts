import type { ResolvedPos } from "prosemirror-model";
import type { NodeSelection } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import { get, writable } from "svelte/store";

export const nodeMenuUpdateId = writable(0);
export const nodeMenuPos = writable<null | number>(null);

/**
 * In an image node, it does not make sense to show the node menu near the caption,
 * we always want to show it near the <figure> element.
 * This function resolves the current position to the most appropriate position to show the node menu.
 */
export function resolveNodeMenuPos(current: ResolvedPos): number {
    if (current.parent.type.name === "figcaption") {
        return current.before(current.depth - 1);
    }
    return current.pos;
}

export function deleteNode(view: EditorView, pos: number) {
    const { state, dispatch } = view;
    const resolvedPos = state.doc.resolve(pos);
    const tr = state.tr.delete(resolvedPos.before(), resolvedPos.after());
    dispatch(tr);
    view.focus();
}

// duplicateNode() {
//     console.log('duplicateNode');
//     const { state, dispatch } = this.view;
//     const { selection } = state;

//     if (!(selection instanceof NodeSelection)) {
//         console.log('selection is not NodeSelection');
//         return;
//     }

//     const nodeToDuplicate = selection.$from.node();

//     if (!nodeToDuplicate) {
//         console.log('nodeToDuplicate is not found');
//         return;
//     }

//     const tr = state.tr;

//     const duplicatedNode = nodeToDuplicate.type.create(
//         nodeToDuplicate.attrs,
//         nodeToDuplicate.content,
//         nodeToDuplicate.marks
//     );

//     const insertionPos = selection.$from.after();
//     tr.insert(insertionPos, duplicatedNode);

//     // Set the selection to the duplicated node
//     const duplicatedNodePos = insertionPos;
//     const newSelection = NodeSelection.create(tr.doc, duplicatedNodePos);
//     tr.setSelection(newSelection);

//     dispatch(tr);
// }