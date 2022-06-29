import Editor from "./ProseMirror/Editor";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";

export default function PostMiddle({id} : {id: number}) {

    const { currentVariant, editorState } = usePostValues(id)
    const { updateCurrentPostVariantValue } = usePostActions(id)

    const isNonDraft = currentVariant.status !== 'draft';

    const content = isNonDraft ? (currentVariant.content_unsaved || currentVariant.content) : currentVariant.content;

    function handleContentUpdate(value: string) {
        const key = isNonDraft ? 'content_unsaved' : 'content'
        updateCurrentPostVariantValue(key, value)
    }

    return <div
        className="post-editor-wrap"
        spellCheck={false}
    >
        <Editor
            id={id}
            value={content || ''}
            currentLanguageId={editorState.languageId}
            onChange={(v: string) => handleContentUpdate(v)}
            editable={currentVariant.status === 'draft' || editorState.isNonDraftEditing}
        />
    </div>

}