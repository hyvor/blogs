import React from "react";
import { usePostActions, usePostValues } from "../helpers";

export default function UnpublishButton({ id }: { id: number }) {

    const { currentVariant } = usePostValues(id);
    const { changeEditorState } = usePostActions(id);
    const hasChanged = currentVariant.content_unsaved !== currentVariant.content;

    let name;
    if (currentVariant.status === 'published' && !hasChanged) {
        name = "Unpublish";
    } else if (currentVariant.status === 'scheduled' && !hasChanged) {
        name = "Unschedule";
    }

    return name ? <button
        className="button small light unpublish-button"
        onClick={() => changeEditorState('isUnpublishing', true)}
    >
        {name}
    </button> : null;
}