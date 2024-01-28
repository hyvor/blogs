import React, {useState} from "react";
import {toast} from "react-toastify";
import {Trash} from "react-bootstrap-icons";
import {Media as MediaType} from "../../types";
import Loader from "../../ReusableComponents/Loader";
import {PopupConfirm} from "../../ReusableComponents/Popup";

type MediaProps = Partial<MediaType> & { isDummy?: boolean, remove?: Function }

export default function Media({ isDummy, id, uploaded_at, url, original_name, extension, remove } : MediaProps) {

    const [deletePopupOpened, setDeletePopupOpened] = useState(false);
    function handleDelete(e: any) {
        e.preventDefault();
        setDeletePopupOpened(true);
    }
    function handleDoDelete() {
        toast("File deleted", {autoClose: 1500});

        remove && remove({id});
        setDeletePopupOpened(false);
    }

    let imageUrl = url;
    let content = null;

    if (['jpg', 'jpeg', 'svg', 'ico', 'png', 'bmp', 'gif'].indexOf(extension || '') === -1) {
        imageUrl = undefined;
        content = <div className="extension">{ extension }</div>;
    }
    if (isDummy) {
        imageUrl = undefined;
        content = <div className="loader-wrap">
            <Loader padding={40} />
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
                        <div className="media-title">{original_name}</div>
                        <div className="media-at">{ uploaded_at && new Date(uploaded_at * 1000).toDateString() }</div>
                    </div> : null
            }


            { !isDummy ?
                <span className="media-delete" onClick={handleDelete}>
                    <Trash size={10} />
                </span> : null
            }
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