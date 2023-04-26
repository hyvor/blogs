import {PopupConfirm} from "../../ReusableComponents/Popup";
import React from "react";
import {usePostActions, usePostValues} from "./helpers";
import {toast} from "react-toastify";
import {Post} from "../../types";

export default function Discarder({id} : {id: number}) {

    const { editorState, currentVariant } = usePostValues(id)
    const { changeEditorState, dicardChanges, loadPost } = usePostActions(id)

    const status = currentVariant.status

    function handleDiscardChanges() {
        changeEditorState('isDiscarding', false);
        dicardChanges();
        loadPost();
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