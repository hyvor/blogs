import React from "react";
import {usePostValues} from "../helpers";

export default function UnpublishButton({id} : {id: number}) {

    const { currentVariant } = usePostValues(id);

    let name;
    if (currentVariant.status === 'published') {
        name = "Unpublish";
    } else if (currentVariant.status === 'scheduled') {
        name = "Unschedule";
    }

    return name ? <button
        className="button small secondary unpublish-button"
        onClick={() => setIsUnPublishing(true)}
    >
        {name}
    </button> : null;
}