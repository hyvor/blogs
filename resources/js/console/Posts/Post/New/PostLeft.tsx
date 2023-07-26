import React from "react";
import Editor from "../ProseMirror/Editor";
import { usePostActions, usePostValues } from "../helpers";
import PostLanguageSelector from "../PostLanguageSelector";
import TitleRow from "../PostTop/TitleRow";
import {useLanguagesValues} from "../../../Settings/Languages/helpers";
import { BoxArrowUpRight } from "react-bootstrap-icons";

export default function PostLeft({id} : {id: number}) {

    return <div className="post-left">

        <div className="post-left-header">

            <div className="left-header-left">
                <PostLanguageSelector id={id} />
            </div>

            <div className="left-header-right" style={{textAlign: "right"}}>

                <button className="button medium light view" style={{marginRight: 8}}>
                    <span>Preview</span>&nbsp;<BoxArrowUpRight />
                </button>
                
                <button className="button medium">Publish</button>

            </div>

        </div>

        <div className="post-left-title">
            <TitleRow id={id} />
        </div>

        <div className="post-left-editor">
            <PostEditor id={id} />
        </div>

    </div>

}


function PostEditor({id} : {id: number}) {

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

    return <Editor
        id={id}
        value={content || ''}
        currentLanguageId={editorState.languageId}
        status={currentVariant.status}
        version={editorState.version}
        onChange={(v: string) => handleContentUpdate(v)}
    />

}