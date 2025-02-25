import type { Mark } from "prosemirror-model";
import type { EditorView, MarkView, MarkViewConstructor } from "prosemirror-view";
import CommentMarkView from "./markview-comment";

interface MarkViewsType {
	[key: string]: MarkViewConstructor;
}


export function getMarkViews(): MarkViewsType {
    return {
        comment(mark: Mark, view: EditorView, inline: boolean): MarkView {
            return new CommentMarkView(mark, view, inline);
        }
    };
}