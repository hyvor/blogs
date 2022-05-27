import {PopupConfirm} from "../../ReusableComponents/Popup";
import React from "react";


export default function Unpublisher() {

    return isUnPublishing ?
        <PopupConfirm
            title={( variant.status === 'published' ? 'Unpublish' : 'Unschedule' ) + " Post"}
            text={"Are you sure to " + ( variant.status === 'published' ? 'unpublish' : 'unschedule' ) + " this post? It will be changed to a draft."}
            name={( variant.status === 'published' ? 'Unpublish' : 'Unschedule' )}
            onClick={handleUnPublish}
            onCancel={() => setIsUnPublishing(false)}
    /> : null

}