import {InfoCircle} from "react-bootstrap-icons";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";

export default function PostBottom({id} : {id:number}) {

    const { savePostAjax, diff, currentVariant  } = usePostValues(id)
    const { savePost } = usePostActions(id)

    const hasChanges = Object.keys(diff).length > 0 || currentVariant.content_unsaved !== currentVariant.content && currentVariant.content_unsaved !== null;

    return <div
        className="post-editor-bottom"
    >
        <div className="post-editor-bottom-content">
        {
                currentVariant.status === 'published' && currentVariant.content !== currentVariant.content_unsaved ?
                <div className="left text-edit">You are editing a published post</div> : <div></div>
            }
            <div className="right">

                <span className="saver">

                    {
                        savePostAjax.status === 'loading' ?
                            "Saving..." :

                            (
                                hasChanges ?
                                    <span className="not-saved" onClick={savePost}>Unsaved changes *</span> :
                                    <span className="saved">Saved</span>
                            )

                    }

                </span>

                <span className="words" id="pm-word-count"/>
                <a target="_blank" href="/docs/writing" className="help">
                    <InfoCircle />
                </a>
            </div>

        </div>
    </div>

}