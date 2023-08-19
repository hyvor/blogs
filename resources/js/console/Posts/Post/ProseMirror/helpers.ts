import { Node } from "prosemirror-model";
import schema from "./schema";
import { EditorView } from "prosemirror-view";


export function getDocFromContent(content: string | null): Node {
    const json = content ? JSON.parse(content) : null;
    return json ? Node.fromJSON(schema, json) : schema.nodes.doc.createAndFill()!;
}


// https://github.com/ueberdosis/tiptap/blob/9dc6b8f1aba105aa5378ec1d391e19ebcb01d8a8/packages/core/src/commands/blur.ts#L17
export function blurEditor(view: EditorView) {
    requestAnimationFrame(() => {
        view.dom.blur();
        window?.getSelection()?.removeAllRanges()
    });
}