import React, { Fragment, useRef, useState} from 'react';
import {ThemeFolder} from "../types";
import {Check, CloudUpload, Plus, X} from "react-bootstrap-icons";
import {useThemeActions} from "./use";
import Loader from "../ReusableComponents/Loader";
import {toast} from "react-toastify";
import {appConfig} from "../helpers";
import byteFormatter from "../../helpers/byteFormatter";

export default function NewFileCreator({ folder } : { folder: ThemeFolder }) {

    const [isTypingName, setIsTypingName] = useState(false);
    const [isCreating, setIsCreating] = useState(false);
    const [fileName, setFileName] = useState('');

    const { createFile } = useThemeActions()

    function handleCreate() {
        setIsCreating(true);
        setIsTypingName(false);
        setFileName('');

        createFile({
            name: fileName,
            folder,
            content: '',
            onCreate: () => {
                setIsCreating(false);
            }
        })
    }

    const uploadRef = useRef<null | HTMLInputElement>(null);

    function handleUploadClick() {
        uploadRef.current?.click();
    }
    async function handleUpload(e: any) {
        const files = (e.target as HTMLInputElement).files;
        if (!files?.length) {
            return toast.error('No files selected');
        }
        const file = files[0];
        const maxSize = appConfig().limits.max_asset_file_size;
        if (file.size > maxSize) {
            return toast.error('File too large. Max size is ' + byteFormatter(maxSize));
        }

        setIsCreating(true);

        createFile({
            name: file.name,
            folder,
            content: file,
            onCreate: () => {
                setIsCreating(false);
            }
        })

    }

    return <div className="file-creator">

        <input
            type="file"
            ref={uploadRef}
            style={{display: 'none'}}
            onChange={handleUpload}
        />

        {
            isCreating ?
                <Loader size={10}/> :
                <Fragment>

                    {
                        !isTypingName &&
                        <Fragment>
                            <span className="new" onClick={() => setIsTypingName(true)}><Plus/> NEW</span>
                            { folder === 'assets' && <span className="new" onClick={handleUploadClick}><CloudUpload /> UPLOAD</span> }
                        </Fragment>
                    }
                    {isTypingName &&
                        <div className="new-file-name">
                            <input
                                name="file-name"
                                type="text"
                                className="input"
                                autoFocus={true}
                                maxLength={255}
                                value={fileName}
                                onChange={e => setFileName(e.target.value)}
                            />
                            <div className="buttons">
                            <span
                                className={"check" + (fileName.trim() === '' ? ' inactive' : '')}
                                onClick={handleCreate}
                            ><Check/></span>
                                <span className="close" onClick={() => setIsTypingName(false)}><X/></span>
                            </div>
                        </div>
                    }


                </Fragment>
        }
    </div>

}