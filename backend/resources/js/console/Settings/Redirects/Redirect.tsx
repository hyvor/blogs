import subdomainLogic from "../../logic/subdomainLogic";
import redirectsLogic from "../../logic/redirectsLogic";
import {useActions } from "kea";
import React, {useState} from "react";
import {toast} from "react-toastify";
import {PencilFill, Trash} from "react-bootstrap-icons";
import {PopupConfirm } from "../../ReusableComponents/Popup";
import {Redirect as RedirectType} from "../../types";
import {TableRowItem, TableRow} from "../../ReusableComponents/Table";
import CreateUpdateRedirectPopup from "./CreateUpdateRedirectPopup";
import getSubdomain from "../../logic-helpers/subdomain";

export default function Redirect ({redirect}: {redirect: RedirectType}){

    const redirectLogicBuilt = redirectsLogic({subdomain: getSubdomain()})
    const { remove } = useActions(redirectLogicBuilt)


    const [ isUpdating, setIsUpdating ] = useState<boolean>(false)
    const [ isDeleting, setIsDeleting ] = useState<boolean>(false)

    function handleDelete() {
        remove({id: redirect.id})
        toast("Redirect deleted");
    }

    return <TableRow>
        <TableRowItem>{ redirect.path }</TableRowItem>
        <TableRowItem><a href={redirect.to} target="_blank" className="link">{ redirect.to }</a></TableRowItem>
        <TableRowItem>{ redirect.type == 'permanent' ? "Permanent" : "Temporary" }</TableRowItem>

        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating ?
                <CreateUpdateRedirectPopup
                    redirect={redirect}
                    onClose={() => setIsUpdating(false)}
                /> : null
        }

        {
            isDeleting ?
                <PopupConfirm
                    title="Delete Redirect"
                    text="Please confirm to delete this redirect"
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                />
                : null
        }
    </TableRow>
}