import React, {useState} from "react";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {Navigation} from "../../types";
import {useLanguagesValues} from "../Languages/helpers";
import {PencilFill, Trash} from "react-bootstrap-icons";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import navigationLogic from "../../logic/navigationLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions} from "kea";
import {toast} from "react-toastify";
import CreateUpdateNavigationPopup from "./CreateUpdateNavigationPopup";

export default function Navigation({ navigation } : { navigation: Navigation }) {

    const navigationLogicInst = navigationLogic({subdomain: getSubdomain()})
    const { remove } = useActions(navigationLogicInst)

    const { primaryLanguage } = useLanguagesValues();

    const [ isUpdating, setIsUpdating ] = useState(false)
    const [ isDeleting, setIsDeleting ] = useState(false)

    function handleDelete() {
        remove({id: navigation.id})
        toast.success("Navigation deleted");
        setIsDeleting(false);
    }

    return <TableRow>
        <TableRowItem>{ navigation.variants[primaryLanguage.id].name }</TableRowItem>
        <TableRowItem>{ navigation.url }</TableRowItem>

        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating &&
            <CreateUpdateNavigationPopup
                navigation={navigation}
                onClose={() => setIsUpdating(false)}
            />
        }

        {
            isDeleting &&
            <PopupConfirm
                title="Delete Navigation"
                text="Please confirm to delete this navigation"
                name="Delete"
                buttonClass="danger"
                onClick={handleDelete}
                onCancel={() => setIsDeleting(false)}
            />
        }

    </TableRow>

}