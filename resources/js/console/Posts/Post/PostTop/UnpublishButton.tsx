import React from "react";
import {usePostActions, usePostValues} from "../helpers";

export default function UnpublishButton({id} : {id: number}) {

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id)

    let name;
    if (currentVariant.status === 'published') {
        name = "Unpublish";
    } else if (currentVariant.status === 'scheduled') {
        name = "Unschedule";
    }

    return name ? <button
        className="button small secondary unpublish-button"
        onClick={() => changeEditorState('isUnpublishing', true)}
    >
        {name}
    </button> : null;
}