import React from 'react';
import {toast} from "react-toastify";
import {useActions} from "kea";
import mediaLogic from "../logic/mediaLogic";
import subdomainLogic from "../logic/subdomainLogic";
import {Media} from "../types";
import getSubdomain from "../logic-helpers/subdomain";

interface ImageSelectorProps {
    src: string | null,
    onChange: (url: string | null) => void
}

export default function ImageSelector({ src, onChange } : ImageSelectorProps) {

    const { uploadImage } = useActions(mediaLogic({subdomain: getSubdomain()}))

    function handleUpload() {
        const uploadIconInput = document.createElement('input')
        uploadIconInput.type = "file";
        uploadIconInput.accept = "image/*";
        uploadIconInput.hidden = true;
        document.body.appendChild(uploadIconInput);
        uploadIconInput.click();
        uploadIconInput.onchange = function(e) {
            const files = (e.target as HTMLInputElement).files;
            if (!files || !files.length) return;
            if (files.length > 1) {
                return toast.error("Please select one file");
            }
            uploadImage({
                file: files[0],
                onUpload: (media: Media) => {
                    onChange(media.url)
                }
            });
        }
    }

    function handleRemove() {
        onChange(null)
    }

    return <div className="global-image-selector">
        {
            src ?
                <div className="image-preview">
                    <div className="preview-top">
                        <img src={src} alt="Image Selector" />
                    </div>
                    <div className="preview-bottom">
                        <button className="button" onClick={handleUpload}>Change</button>
                        <button className="button danger" onClick={handleRemove}>Remove</button>
                    </div>
                </div> :
                <div>
                    <button className="button medium upload-button" onClick={handleUpload}>Upload</button>
                </div>
        }
    </div>

}