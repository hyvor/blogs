import {Check, X} from "react-bootstrap-icons";
import React, {useState} from "react";

interface FileNameInputProps {
    name?: string,
    onCreate: (name: string) => any,
    onCancel: Function
}

export default function FileNameInput({ name = '', onCreate, onCancel } : FileNameInputProps) {

    const [editedName, setEditedName] = useState(name)

    return <div className="file-name-input">
        <input
            name="file-name"
            type="text"
            className="input"
            autoFocus={true}
            maxLength={255}
            value={editedName}
            onChange={e => setEditedName(e.target.value)}
        />
        <div className="buttons">
            <span
                className={"check" + (editedName.trim() === '' ? ' inactive' : '')}
                onClick={() => onCreate(editedName)}
            ><Check/></span>
            <span className="close" onClick={() => onCancel()}><X/></span>
        </div>
    </div>

}