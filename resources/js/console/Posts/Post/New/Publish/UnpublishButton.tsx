import React, { Fragment, useState } from "react";
import { usePostActions, usePostValues } from "../../helpers";
import { PopupConfirm } from "../../../../ReusableComponents/Popup";
import { toast } from "react-toastify";
import { bringLeftHeaderToFront } from "../z-index";


export default function UnpublishButton({id} : {id: number}) {

    const { currentVariant } = usePostValues(id);
    const { saveCurrentVariantDiff } = usePostActions(id);

    const [isUnpublishing, setIsUnpublishing] = useState(false);
    const [hasUnpublishingStarted, setHasUnpublishingStarted] = useState(false);

    const status = currentVariant.status;

    if (status !== 'published' && status !== 'scheduled') {
        return null;
    }

    function handleUnpublish() {

        setHasUnpublishingStarted(true);

        saveCurrentVariantDiff({
            diff: {
                status: 'draft',
            },
            onSave: () => {
                toast("Post unpublished");
                setIsUnpublishing(false);
                setHasUnpublishingStarted(false);
            }
        });

    }

    return <Fragment>
        <button
            className="button medium light unpublish-button"
            onClick={() => {
                bringLeftHeaderToFront();
                setIsUnpublishing(true)
            }}
        >
            { status === 'published' ? "Unpublish" : "Unschedule" }
        </button>
        
        {
            isUnpublishing && <PopupConfirm
                title={( status === 'published' ? 'Unpublish' : 'Unschedule' ) + " Post"}
                text={"Are you sure to " + ( status === 'published' ? 'unpublish' : 'unschedule' ) + " this post? It will be changed to a draft."}
                name={( status === 'published' ? 'Unpublish' : 'Unschedule' )}
                onClick={handleUnpublish}
                onCancel={() => setIsUnpublishing(false)}
                isLoading={hasUnpublishingStarted}
                loadingName="Unpublishing"
            />
        }

    </Fragment>

}