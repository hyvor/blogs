import {Check, X} from "react-bootstrap-icons";
import React, {KeyboardEventHandler, useState} from "react";

interface FileNameInputProps {
    name?: string,
    onCreate: (name: string) => any,
    onCancel: Function
}

export default function FileNameInput({ name = '', onCreate, onCancel } : FileNameInputProps) {

    const [editedName, setEditedName] = useState(name || '')
    const isEditedNameEmpty = editedName.trim() === '';

    const handleKeyUp: KeyboardEventHandler<HTMLInputElement> = (e) => {
        if (e.key === 'Enter' && !isEditedNameEmpty) {
            onCreate(editedName)
        }
        if (e.key === 'Escape') {
            onCancel();
        }
    }

    return <div className="file-name-input">
        <input
            name="file-name"
            type="text"
            className="input"
            autoFocus={true}
            maxLength={255}
            value={editedName}
            onChange={e => setEditedName(e.target.value)}
            onKeyUp={handleKeyUp}
        />
        <div className="buttons">
            <span
                className={"check" + (isEditedNameEmpty ? ' inactive' : '')}
                onClick={() => onCreate(editedName)}
            ><Check/></span>
            <span className="close" onClick={() => onCancel()}><X/></span>
        </div>
    </div>

}