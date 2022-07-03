import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {PencilFill, Trash} from "react-bootstrap-icons";
import React, {useState} from "react";
import {Tag as TagType} from "../../types";
import {useValues} from "kea";
import languagesLogic from "../../logic/languagesLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {useTagsActions} from "./useTags";
import {toast} from "react-toastify";
import UpdateTagPopup from "./UpdateTagPopup";

export default function Tag({ tag } : {tag: TagType}) {

    const { primaryLanguage } = useValues(languagesLogic({subdomain: getSubdomain()}))

    const { remove } = useTagsActions();

    const variant = tag.variants.find(v => v.language_id === primaryLanguage.id);

    const [ isUpdating, setIsUpdating ] = useState(false);
    const [ isDeleting, setIsDeleting ] = useState(false);

    function handleDelete() {
        remove({id: tag.id})
        toast("Tag deleted")
        setIsDeleting(false)
    }

    return <TableRow>
        <TableRowItem>
            <div>{ variant?.name }</div>
            <div className="slug-row">
                <a href={variant?.url} target="_blank" className="link">{ tag.slug }</a>
            </div>
        </TableRowItem>
        <TableRowItem>{ variant?.description }</TableRowItem>
        <TableRowItem>{ tag.posts_count }</TableRowItem>
        <TableRowItem>
            <button className="icon-button" onClick={() => setIsUpdating(true)}><PencilFill size={10} /></button>
            <button className="icon-button" onClick={() => setIsDeleting(true)}><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating &&
            <UpdateTagPopup tag={tag} onClose={() => setIsUpdating(false)} />
        }

        {
            isDeleting ?
                <PopupConfirm
                    title="Delete Tag"
                    text="Please confirm to delete this tag"
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                />
                : null
        }

    </TableRow>

}