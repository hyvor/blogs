import React, {Fragment, useRef, useState} from "react";
import {CloudUpload} from "react-bootstrap-icons";
import {toast} from "react-toastify";
import {appConfig} from "../helpers";
import {useActions} from "kea";
import themeLogic from "../logic/themeLogic";
import getSubdomain from "../logic-helpers/subdomain";
import Spinner from "../ReusableComponents/Spinner";


export default function Upload() {

    const { uploadTheme } = useActions(themeLogic({subdomain: getSubdomain()}));

    const inputRef = useRef<HTMLInputElement | null>(null)
    const [ isUploading, setIsUploading ] = useState(false)

    function handleClick() {
        (inputRef.current as HTMLInputElement).click()
    }

    function handleUpload() {
        const files = (inputRef.current as HTMLInputElement).files;
        if (!files || files.length !== 1) {
            return toast.error("Please select a file");
        }
        const file = files[0];

        const max = appConfig().limits.max_theme_zip_size_kb;
        if (file.size > max * 1000) {
            return toast.error("Max file size is" + (max / 1000) + "MB");
        }

        setIsUploading(true)
        uploadTheme({
            zip: file,
            onUpload: () => {
                setIsUploading(false)
                toast.success("Theme uploading completed");
            }
        });
    }

    return <Fragment>
        <button
            onClick={handleClick}
            className="button inactive small"
        >{ isUploading ? "Uploading" : "Upload" }&nbsp;{ isUploading ? <Spinner size={7} dark={true} /> : <CloudUpload /> }</button>
        {/* https://stackoverflow.com/a/56357139/9059939 */}
        <input
            ref={inputRef}
            type="file"
            hidden={true}
            accept="zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed"
            onChange={handleUpload}
        />
    </Fragment>

}