import React, {useState} from "react";
import {toast} from "react-toastify";
import {PencilFill, Trash} from "react-bootstrap-icons";
import {PopupConfirm } from "../../ReusableComponents/Popup";
import {Webhook as WebhookType} from "../../types";
import {TableRowItem, TableRow} from "../../ReusableComponents/Table";
import CreateUpdateWebhookPopup from "./CreateUpdateWebhookPopup";
import copyTextToClipboard from "../../../helpers/copyToClipboard";
import {useWebhooksActions} from "../../logic-helpers/webhooks";

export default function Webhook({webhook}: {webhook: WebhookType}) {

    const { remove } = useWebhooksActions()

    const [isUpdating, setIsUpdating] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false)

    function handleDelete() {
        remove({id: webhook.id})
        toast("Webhook deleted");
    }

    return <TableRow>
        <TableRowItem>{ webhook.url }</TableRowItem>
        <TableRowItem>
            {
                webhook.events.map(event => <span className="event">{event}</span>)
            }
        </TableRowItem>
        <TableRowItem><button
            className="button small"
            onClick={() => {copyTextToClipboard(webhook.secret); toast("Copied")}}
        >COPY</button></TableRowItem>
        <TableRowItem>
            <button
                className="icon-button"
                onClick={() => setIsUpdating(true)}
            ><PencilFill size={10} /></button>
            <button
                className="icon-button"
                onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating &&
            <CreateUpdateWebhookPopup
                webhook={webhook}
                onClose={() => setIsUpdating(false)}
            />
        }

        {
            isDeleting &&
            <PopupConfirm
                title="Delete Webhook"
                text="Please confirm to delete this webhook"
                name="Delete"
                buttonClass="danger"
                onClick={handleDelete}
                onCancel={() => setIsDeleting(false)}
            />
        }
    </TableRow>
}