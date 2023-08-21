import { EditorView } from "prosemirror-view";



export interface PostEditorState {
    languageId: number,
    isFullscreen: boolean,
    //isPublishing: boolean,
    //isUnpublishing: boolean,
    isDiscarding: boolean,
    // isNonDraftEditing: boolean,
    isNonDraftUpdating: boolean,

    /**
     * Saving the post (title and content of the current variant) in useSave.ts
     */
    isSaving: boolean,

    /**
     * Used to force a re-render of the editor when the post is updated.
     * Ex: when automatically changing the content
     */
    version: number,

    editorView: EditorView | null,
}