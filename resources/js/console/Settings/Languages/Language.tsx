import React, {useState} from "react";
import {PencilFill, Trash, TrashFill} from "react-bootstrap-icons";
import {Language} from "../../types";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import CreateUpdateLanguagePopup from "./CreateUpdateLanguagePopup";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";

interface LanguageProps {
    language: Language,
    remove: Function
}

export default function Language({language, remove} : LanguageProps) {

    const [isUpdating, setIsUpdating] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    function handleDelete() {
        remove({id: language.id});
        setIsDeleting(false)
    }

    return <TableRow>
        <TableRowItem>
            <span>{language.name}</span>
            {
                language.is_primary ?
                    <span className="default-tag">PRIMARY</span>
                    : null
            }
        </TableRowItem>
        <TableRowItem>{language.code}</TableRowItem>

        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            <button
                className="icon-button"
                onClick={() => setIsDeleting(true)}
                style={{visibility: language.is_primary ? "hidden" : "visible"}}
            ><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating ?
                <CreateUpdateLanguagePopup
                    language={language}
                    onCancel={() => setIsUpdating(false)}
                /> : null
        }

        {
            isDeleting ?
                <PopupConfirm
                    title="Delete Language"
                    text="Are you sure to delete this language from this blog?"
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                /> : null
        }

    </TableRow>

}