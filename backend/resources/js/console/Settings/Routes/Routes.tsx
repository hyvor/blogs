import React, {Fragment, useState} from 'react';
import { useActions, useValues } from 'kea';
import { Trash, PencilFill, Plus } from 'react-bootstrap-icons';
import {toast} from 'react-toastify'
import subdomainLogic from '../../logic/subdomainLogic';
import routesLogic from '../../logic/routesLogic';
import Loader from '../../ReusableComponents/Loader';
import Toast from '../../ReusableComponents/Toast';
import NoResults from '../../ReusableComponents/NoResults';
import Input from '../../ReusableComponents/Input';
import { Popup, PopupBodyDefault, PopupConfirm, PopupFooterDoubleButton, PopupHeaderDefault } from '../../ReusableComponents/Popup';
import DualSetting from '../../ReusableComponents/DualSetting';
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import Route from "./Route";
import CreateUpdateRoutePopup from "./CreateUpdateRoutePopup";
import getSubdomain from "../../logic-helpers/subdomain";

export default function Routes() {

    const routeLogicBuilt = routesLogic({subdomain: getSubdomain()})
    const { routes, loadAjax, createAjax } = useValues(routeLogicBuilt)

    const [isCreating, setIsCreating] = useState(false);

    return <div className="settings-routes">
        <div className="title">
            Routes <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >Create <Plus /></button>
        </div>
        <div>
            {
                loadAjax.status === 'loading' ?
                    <Loader padding={100}/> :
                    (routes.length > 0 ?
                        <Table>
                            <TableHead>
                                <TableHeadItem>Route Name</TableHeadItem>
                                <TableHeadItem>Match</TableHeadItem>
                                <TableHeadItem>Template</TableHeadItem>
                                <TableHeadItem>Posts Filter</TableHeadItem>
                                <TableHeadItem>Content Type</TableHeadItem>
                                <div />
                            </TableHead>
                            <Fragment>
                                {
                                    routes.map(route =>
                                        <Route
                                            key={route.id}
                                            route={route}
                                        />
                                    )
                                }
                            </Fragment>
                        </Table>
                        :
                        <NoResults
                            text="There are no routes."
                            padding={40}
                            imageWidth={250}
                        />
                    )
            }

        </div>

        {
            isCreating ? <CreateUpdateRoutePopup onClose={() => setIsCreating(false)} /> : null
        }

    </div>

}