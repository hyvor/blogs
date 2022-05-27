import {InfoCircle} from "react-bootstrap-icons";
import React from "react";
import {usePostValues} from "./helpers";

export default function PostBottom({id} : {id:number}) {

    const { savePostAjax  } = usePostValues(id)

    return <div
        className="post-editor-bottom"
    >
        <div className="post-editor-bottom-content">
            <div className="right">
                {
                    savePostAjax.status === 'loading' ?
                        <span className="saving">Saving...</span> : null
                }
                <span className="words" id="pm-word-count"/>
                <a target="_blank" href="/docs/writing" className="help">
                    <InfoCircle />
                </a>
            </div>
        </div>
    </div>

}