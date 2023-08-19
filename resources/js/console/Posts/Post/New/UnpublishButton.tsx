import React, { Fragment, useState } from "react";
import { usePostValues } from "../helpers";


export default function UnpublishButton({id} : {id: number}) {

    const { currentVariant } = usePostValues(id);
    const [isUnpublishing, setIsUnpublishing] = useState(false);

    const status = currentVariant.status;

    if (status !== 'published' && status !== 'scheduled') {
        return null;
    }

    return <Fragment>
        <button
            className="button medium light unpublish-button"
            onClick={() => setIsUnpublishing(true)}
        >
            { status === 'published' ? "Unpublish" : "Unschedule" }
        </button>
        
        {
            isUnpublishing
        }

    </Fragment>

}