import {useActions} from "kea";
import React, {useState} from "react";
import {PencilFill, Trash} from "react-bootstrap-icons";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {Route as RouteType} from "../../types";
import routesLogic from "../../logic/routesLogic";
import {TableRow, TableRowItem} from "../../ReusableComponents/Table";
import CreateUpdateRoutePopup from "./CreateUpdateRoutePopup";
import getSubdomain from "../../logic-helpers/subdomain";

export default function Route({route}: {route: RouteType}){

    const routeLogicInst = routesLogic({subdomain: getSubdomain()})
    const { remove } = useActions(routeLogicInst)

    const [isUpdating, setIsUpdating] = useState<boolean>(false);
    const [isDeleting, setIsDeleting] = useState<boolean>(false);

    function handleDelete() {
        remove({id: route.id});
    }

    const isDefaultRoute = ['post', 'page', 'index', 'tag', 'author'].indexOf(route.name) >= 0;

    return <TableRow>
        <TableRowItem>{ route.name }</TableRowItem>
        <TableRowItem>{ route.match }</TableRowItem>
        <TableRowItem>{ route.template }</TableRowItem>
        <TableRowItem>{ route.posts_filter }</TableRowItem>
        <TableRowItem>{ route.content_type }</TableRowItem>

        <TableRowItem>
            <button
                className="icon-button"
                onClick={() => setIsUpdating(true)}
            ><PencilFill size={10} /></button>
            <button
                className="icon-button"
                onClick={() => setIsDeleting(true)}
                style={{visibility: isDefaultRoute ? "hidden" : "visible"}}
            ><Trash size={10} /></button>
        </TableRowItem>

        {
            isUpdating ?
                <CreateUpdateRoutePopup
                    onClose={() => setIsUpdating(false)}
                    route={route}
            /> : null
        }

        {
            isDeleting ?
                <PopupConfirm
                    title="Delete Route"
                    text="Are you sure to delete this route?"
                    name="Delete"
                    buttonClass="danger"
                    onClick={handleDelete}
                    onCancel={() => setIsDeleting(false)}
                />
                : null
        }

    </TableRow>

}