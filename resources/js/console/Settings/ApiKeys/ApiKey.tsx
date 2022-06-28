import React, {useState} from "react";
import {toast} from "react-toastify";
import {PencilFill, Trash} from "react-bootstrap-icons";
import {PopupConfirm } from "../../ReusableComponents/Popup";
import {ApiKey } from "../../types";
import {TableRowItem, TableRow} from "../../ReusableComponents/Table";
import {useApiKeysActions} from "../../logic-helpers/api-keys";

export default function ApiKey ({apiKey}: {apiKey: ApiKey}) {

    const { remove } = useApiKeysActions()

    const [ isDeleting, setIsDeleting ] = useState(false)

    function handleDelete() {
        remove({id: apiKey.id})
        toast("API Key deleted");
    }

    return <TableRow>
        <TableRowItem>{ apiKey.name }</TableRowItem>
        <TableRowItem>{ apiKey.type[0].toUpperCase() + apiKey.type.substr(1) } API</TableRowItem>
        <TableRowItem><button className="button small">COPY</button></TableRowItem>

        <TableRowItem>
            <button
                className="icon-button"
                onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>

        {
            isDeleting ?
                <PopupConfirm
                    title="Delete API Key"
                    text="Please confirm to delete this API Key"
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                />
                : null
        }
    </TableRow>
}