import {PopupConfirm} from "../../ReusableComponents/Popup";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";
import {toast} from "react-toastify";
import {PostVariant} from "../../types";

export default function Unpublisher({id} : {id: number}) {

    const { editorState, currentVariant } = usePostValues(id)
    const { forceSavePost, changeEditorState } = usePostActions(id)

    const status = currentVariant.status

    function handleUnPublish() {
        const update = {variants: {[editorState.languageId]: {status: 'draft'}} as Partial<PostVariant>};
        forceSavePost({
            update,
            onSave: () => {
                toast("Post unpublished")
            }
        });
        changeEditorState('isUnpublishing', false);
    }

    return editorState.isUnpublishing ?
        <PopupConfirm
            title={( status === 'published' ? 'Unpublish' : 'Unschedule' ) + " Post"}
            text={"Are you sure to " + ( status === 'published' ? 'unpublish' : 'unschedule' ) + " this post? It will be changed to a draft."}
            name={( status === 'published' ? 'Unpublish' : 'Unschedule' )}
            onClick={handleUnPublish}
            onCancel={() => changeEditorState('isUnpublishing', false)}
    /> : null

}