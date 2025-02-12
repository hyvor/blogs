import { writable } from "svelte/store";

export const nodeMenuUpdateId = writable(0);
export const nodeMenuPos = writable<null | number>(null);

// deleteNode() {
//     const { state, dispatch } = this.view;
//     const selection = state.selection as NodeSelection;
//     const tr = state.tr.delete(selection.$from.before(), selection.$from.after());
//     dispatch(tr);
// }

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