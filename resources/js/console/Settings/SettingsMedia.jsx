import { useActions, useValues } from 'kea';
import { A } from 'kea-router';
import React, { useState } from 'react';
import mediaLogic from '../logic/mediaLogic';
import subdomainLogic from '../logic/subdomainLogic';
import Loader from '../ReusableComponents/Loader';
import {toast} from 'react-toastify'
import { Trash, Upload } from 'react-bootstrap-icons';
import { PopupConfirm } from '../ReusableComponents/Popup';
import Toast from '../ReusableComponents/Toast';
import NoResults from '../ReusableComponents/NoResults';

let uploadInput = null;

export default function SettingsMedia() {

    const subdomain = subdomainLogic.values.subdomain;
    const mediaLogicBuilt = mediaLogic({subdomain})
    const { media, loadAjax, uploadAjax } = useValues(mediaLogicBuilt)
    const { remove, upload } = useActions(mediaLogicBuilt)

    function handleUpload() {

        if (!uploadInput) {
            uploadInput = document.createElement('input')
            uploadInput.type = "file";
            uploadInput.hidden = true;
            document.body.appendChild(uploadInput);
            uploadInput.click();
            uploadInput.onchange = function(e) {
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

    return <div className="setting-media">

        <div className="title">
            Media <button 
                onClick={uploadAjax.status === 'loading' ? null: handleUpload}
                className="button small inactive">
                    {uploadAjax.status === 'loading' ? "Uploading..." : <span>Upload <Upload /></span> }
            </button>
        </div>

        <div className="media-view">
            {

                loadAjax.status === 'loading' ?
                <Loader style={{padding:200, textAlign: 'center'}} /> :

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
                                x={console.log(uploadAjax.error)}
                                text={uploadAjax.error}
                                type="error"
                            /> : null
                        }

                        {
                            media.map(m => <Media {...m} remove={remove} key={m.id} />)
                        }
                    </div> : <NoResults 
                                text="No media found"
                            />
                    )
            }
        </div>

    </div>

}

function Media({ isDummy, id, uploaded_at, url, name, extension, remove }) {

    const [deletePopupOpened, setDeletePopupOpened] = useState(false);
    function handleDelete(e) {
        e.preventDefault();
        setDeletePopupOpened(true);
    }
    function handleDoDelete() {
        toast("File deleted", {autoClose: 1500});

        remove({id});
        setDeletePopupOpened(false);
    }

    let imageUrl = url;
    let content = null;


    if (['jpg', 'jpeg', 'svg', 'ico', 'png', 'bmp', 'gif'].indexOf(extension) === -1) {
        imageUrl = null;
        content = <div className="extension">{ extension }</div>;
    }
    if (isDummy) {
        imageUrl = null;
        content = <div className="loader-wrap">
            <Loader />
        </div>
    }

    return <div className="media-item-view">
        <a className="media-wrap" href={url} target="_blank">
            <div 
                className="img-wrap"
                style={imageUrl ? {
                    backgroundImage: 'url(' + imageUrl + ')'
                } : {}}
            >
                { content }
            </div>

            {
                !isDummy ?
                <div className="media-data">
                    <div className="media-title">{name}</div>
                    <div className="media-at">{ new Date(uploaded_at * 1000).toDateString() }</div>
                </div> : null
            }


            { !isDummy ?
                <span className="media-delete" onClick={handleDelete}>
                    <Trash size={10} />
                </span> : null }
        </a>


        {
            deletePopupOpened ?
            <PopupConfirm
                title="Delete Permanently"
                text="Are you sure to delete this media item permanently? You will not be able to access it anymore."
                name="Delete"
                buttonClass="danger"
                onClick={handleDoDelete}
                onCancel={() => setDeletePopupOpened(false)}
            />
            : null
        }
    </div>

}