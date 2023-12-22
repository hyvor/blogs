/* import Editor from "./ProseMirror/Editor";
import React from "react";
import { usePostActions, usePostValues } from "./helpers";
import PostLanguageSelector from "./PostLanguageSelector";
import TitleRow from "./PostTop/TitleRow";
import {useLanguagesValues} from "../../Settings/Languages/helpers";

export default function PostMiddle({ id }: { id: number }) {

    const { currentVariant, editorState } = usePostValues(id)
    const { updateCurrentPostVariantValue } = usePostActions(id)

    const { getLanguageById } = useLanguagesValues();
    const language = getLanguageById(editorState.languageId);
    const isRtl = language ? language.direction === 'rtl' : false;

    const isNonDraft = currentVariant.status !== 'draft';

    const content = isNonDraft ? (currentVariant.content_unsaved || currentVariant.content) : currentVariant.content;

    function handleContentUpdate(value: string) {
        const key = isNonDraft ? 'content_unsaved' : 'content'
        updateCurrentPostVariantValue(key, value)
    }

    const isEditable = currentVariant.status === 'draft' || editorState.isNonDraftEditing;

    return <div
        className={"post-editor-wrap" + (isEditable ? "" : " non-editable")}
        spellCheck={false}
        dir={isRtl ? 'rtl' : 'ltr'}
        style={isRtl ? {
            direction: 'rtl',
            textAlign: 'right'
        } : undefined}
    >
        <div className="post-editor-headers">
            <TitleRow id={id} />
        </div>

        <Editor
            id={id}
            value={content || ''}
            currentLanguageId={editorState.languageId}
            status={currentVariant.status}
            version={editorState.version}
            onChange={(v: string) => handleContentUpdate(v)}
        />
    </div>

} */