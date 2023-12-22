import { useActions, useValues } from 'kea';
import React  from 'react';
import mediaLogic from '../../logic/mediaLogic';
import Loader from '../../ReusableComponents/Loader';
import {toast} from 'react-toastify'
import { Upload } from 'react-bootstrap-icons';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import { useEffect } from 'react';
import Media from "./Media";
import type { Media as MediaType } from '../../types'
import getSubdomain from "../../logic-helpers/subdomain";

let uploadInput: any = null;

export default function SettingsMedia() {

    const mediaLogicBuilt = mediaLogic({subdomain: getSubdomain()})
    const { media, loadAjax, uploadAjax } = useValues(mediaLogicBuilt)
    const { remove, upload, load } = useActions(mediaLogicBuilt)

    function handleUpload() {

        if (!uploadInput) {
            uploadInput = document.createElement('input')
            uploadInput.type = "file";
            uploadInput.hidden = true;
            document.body.appendChild(uploadInput);
            uploadInput.click();
            uploadInput.onchange = function(e: any) {
                const files = e.target.files;
                if (!files.length) return;
                if (files.length > 1) {
                    return toast.error("Only one file allowed");
                }
                upload({file: files[0]});
            }
        } else {
            uploadInput.click();
        }

    }

    useEffect(() => load({}), [])

    return <div className="setting-media">

        <div className="title">
            Media <button 
                onClick={uploadAjax.status === 'loading' ? undefined: handleUpload}
                className="button small inactive">
                    {uploadAjax.status === 'loading' ? "Uploading..." : <span>Upload <Upload /></span> }
            </button>
        </div>

        <div className="media-view">
            {

                loadAjax.status === 'loading' ?
                <Loader padding={200} /> :

                (
                    media.length ? 
                    
                    <div className="settings-media-wrap">
                        {
                            uploadAjax.status === 'loading' ?
                            <Media isDummy={true} /> : null
                        }
                        {
                            uploadAjax.status === 'error' ?
                            <Toast
                                text={uploadAjax.error || ''}
                                type="error"
                            /> : null
                        }

                        {
                            media.map((m: MediaType) => <Media {...m} remove={remove} key={m.id} />)
                        }
                    </div> :
                        <NoResults
                            text="No media found"
                        />
                    )
            }
        </div>

    </div>

}
