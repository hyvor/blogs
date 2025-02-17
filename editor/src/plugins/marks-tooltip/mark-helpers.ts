import type { Mark, ResolvedPos } from "prosemirror-model";

export function markExtend ($start: ResolvedPos, mark: Mark) {
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