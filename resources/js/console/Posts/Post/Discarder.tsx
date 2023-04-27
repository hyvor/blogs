import {PopupConfirm} from "../../ReusableComponents/Popup";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";
import {toast} from "react-toastify";
import {Post} from "../../types";

export default function Discarder({id} : {id: number}) {

    const { editorState, currentVariant } = usePostValues(id)
    const { changeEditorState, discardChanges, loadPost } = usePostActions(id)

    const status = currentVariant.status

    function handleDiscardChanges() {
        discardChanges();
        changeEditorState('isDiscarding', false);
        changeEditorState('version', editorState.version + 1);
    }

    return editorState.isDiscarding ?
        <PopupConfirm
            title={"Discard Changes"}
            text={"Are you sure to discard changes ? All changes will be lost."}
            name={"Discard Changes"}
            onClick={handleDiscardChanges}
            onCancel={() => changeEditorState('isDiscarding', false)}
    /> : null

}