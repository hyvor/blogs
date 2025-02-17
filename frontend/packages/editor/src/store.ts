import type { EditorView } from "prosemirror-view";
import { writable } from "svelte/store";

export interface Props {
    value?: string | null;
}

export interface Store {
    props: Props;
    view: EditorView;
}

export const editorStore = writable<Store>();
export const editorContent = writable<string | null>(null);