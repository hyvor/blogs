


export interface PostEditorState {
    languageId: number,
    isFullscreen: boolean,
    // isChangingSettings: boolean,
    isPublishing: boolean,
    isUnpublishing: boolean,
    isDiscarding: boolean,
    isNonDraftEditing: boolean,
    isNonDraftUpdating: boolean,

    /**
     * Used to force a re-render of the editor when the post is updated.
     * Ex: when automatically changing the content
     */
    version: number,
}