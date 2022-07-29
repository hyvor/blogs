import React, {useState} from 'react';
import {ThemeFolder} from "../types";
import {Check, Plus, X} from "react-bootstrap-icons";

export default function NewFileCreator({ folder } : { folder: ThemeFolder }) {

    const [isCreating, setIsCreating] = useState(false);
    const [fileName, setFileName] = useState('');

    function handleCreate() {



    }

    return <div className="file-creator">
        { !isCreating && <span className="new" onClick={() => setIsCreating(true)}><Plus /> NEW</span> }
        { isCreating &&
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
                    ><Check /></span>
                    <span className="close" onClick={() => setIsCreating(false)}><X /></span>
                </div>
            </div>
        }
    </div>

}