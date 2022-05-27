import TextareaAutosize from "react-textarea-autosize";
import React from "react";
import {usePostActions, usePostValues} from "../helpers";


export default function TitleRow({id} : {id: number}) {

    const { currentVariant } = usePostValues(id);
    const { updateCurrentPostVariantValue } = usePostActions(id);

    return <div className="post-editor-title-row">

        <div className="title-textarea-wrap">
            <TextareaAutosize
                className="post-editor-title"
                placeholder="Title..."
                value={currentVariant.title || ""}
                onChange={e => updateCurrentPostVariantValue('title', e.target.value)}
            />
        </div>

        <div className="status">
            <span className={`global-post-status ${currentVariant.status} large`}>{currentVariant.status}</span>
        </div>
    </div>

}