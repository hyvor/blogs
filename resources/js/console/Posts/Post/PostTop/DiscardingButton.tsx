import React from "react";
import { usePostActions, usePostValues } from "../helpers";

export default function DiscardingButton({ id }: { id: number }) {

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id)
    
    let name;
    if (currentVariant.status === 'published')
        name = "Discard changes";

    return name ? <button
        className="button small light unpublish-button"
        onClick={() => changeEditorState('isDiscarding', true)}
    >
        {name}
    </button> : null;
}