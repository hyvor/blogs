import React from "react";
import { usePostActions, usePostValues } from "../helpers";

export default function DiscardingButton({ id }: { id: number }) {

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id)
    const hasChanged = currentVariant.content_unsaved !== currentVariant.content;
    
    let name;
    if (currentVariant.status === 'published' && hasChanged)
        name = "Discard changes";

    return name ? <button
        className="button small light unpublish-button"
        onClick={() => changeEditorState('isDiscarding', true)}
    >
        {name}
    </button> : null;
}