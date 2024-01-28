import {PopupConfirm} from "../../ReusableComponents/Popup";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";
import {toast} from "react-toastify";
import {Post} from "../../types";

export default function Discarder({id} : {id: number}) {

    const { editorState, currentVariant } = usePostValues(id)
    const { changeEditorState, saveCurrentVariantDiff, updateCurrentPostVariantValue } = usePostActions(id)

    function handleDiscardChanges() {

        updateCurrentPostVariantValue("content_unsaved", null);

        changeEditorState('isSaving', true);
        saveCurrentVariantDiff({
            diff: {
                // content: currentVariant.content,
                content_unsaved: null,
                // title: currentVariant.title
            },
            onSave: () => {
                changeEditorState('isSaving', false);
            }
        })

        changeEditorState('isDiscarding', false);
        changeEditorState('version', editorState.version + 1);
        toast.success(
            <div>Successfully discarded.</div>,
            {
                autoClose: 5000
            }
        )
    }

    return editorState.isDiscarding ?
        <PopupConfirm
            title={"Discard Changes"}
            text={"Are you sure to discard changes? All changes will be lost. The post will be reset to the published version."}
            name={"Discard Changes"}
            onClick={handleDiscardChanges}
            onCancel={() => changeEditorState('isDiscarding', false)}
    /> : null

}