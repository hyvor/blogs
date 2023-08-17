import { Node } from "prosemirror-model";
import schema from "./schema";


export function getDocFromContent(content: string | null): Node {
    const json = content ? JSON.parse(content) : null;
    return json ? Node.fromJSON(schema, json) : schema.nodes.doc.createAndFill()!;
}