import React, {Fragment, useState} from "react";
import {PopupConfirm} from "../ReusableComponents/Popup";
import {getEndpoint} from "../lib/api";
import getSubdomain from "../logic-helpers/subdomain";
import {CloudDownload} from "react-bootstrap-icons";

export default function Download() {

    const [ isDownloading, setIsDownloading ] = useState(false);

    function handleDownload() {
        window.open(getEndpoint(getSubdomain(), '/theme/download'));
        setIsDownloading(false)
    }

    return <Fragment>
        <button onClick={() => setIsDownloading(true)} className="button inactive small">Download <CloudDownload /></button>
        {
            isDownloading ?
                <PopupConfirm
                    title="Download Theme"
                    text="Download your theme as a ZIP file."
                    name="Download"
                    onClick={handleDownload}
                    onCancel={() => setIsDownloading(false)}
                />
            : null
        }
    </Fragment>

}