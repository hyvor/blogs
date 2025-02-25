import type { EditorView } from "prosemirror-view";
import { writable } from "svelte/store";

export interface Props {

    /**
     * The initial value of the editor.
     */
    value?: string | null;

    /**
     * Listen to DOM events of the Prosemirror editor.
     * Only focus and blur are supported.
     */
    ondomevent?: (name: keyof HTMLElementEventMap, event: Event) => void;

    /**
     * Called when the editor's content changes. value is in ProseMirror JSON format.
     */
    onvaluechange?: (value: string) => void;


    /**
     * Whether the current language is right-to-left.
     */
    rtl?: boolean;
    

}

export interface Store {
    props: Props;
    view: EditorView;
}

export const editorStore = writable<Store>();
export const editorContent = writable<string | null>(null);