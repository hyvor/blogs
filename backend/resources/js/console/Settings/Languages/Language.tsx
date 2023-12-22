import React, {useState} from "react";
import {PencilFill, Trash } from "react-bootstrap-icons";
import {Language as LanguageType} from "../../types";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import CreateUpdateLanguagePopup from "./CreateUpdateLanguagePopup";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import Input from "../../ReusableComponents/Input";
import {toast} from "react-toastify";

interface LanguageProps {
    language: LanguageType,
    remove: Function
}

export default function Language({language, remove} : LanguageProps) {

    const [isUpdating, setIsUpdating] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);

    function handleDelete() {

        if (language.code !== deleteConfirmCode) {
            return toast.error("Please type the language code correctly");
        }

        remove({id: language.id});
        setIsDeleting(false)
    }

    const [ deleteConfirmCode, setDeleteConfirmCode ] = useState<string>('');

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
        <TableRowItem>{language.direction.toUpperCase()}</TableRowItem>

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
                    text={
                        <div>
                            <p>
                                Are you sure to delete the <b>{ language.name }</b> language? This will permanently delete all { language.name } translations in:
                            </p>
                            <ul>
                                <li>Blog (name and description)</li>
                                <li>Posts</li>
                                <li>Users</li>
                                <li>Tags</li>
                                <li>Navigations</li>
                            </ul>
                            <p>Please type the language code (<b>{language.code}</b>) below to confirm the delete</p>
                            <Input
                                value={deleteConfirmCode}
                                onChange={setDeleteConfirmCode}
                                placeholder="Type language code"
                            />
                        </div>
                    }
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                /> : null
        }

    </TableRow>

}