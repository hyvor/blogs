import React, {Fragment, useState} from 'react';
import {useApiKeysValues} from "../../logic-helpers/api-keys";
import {Plus} from "react-bootstrap-icons";
import Loader from "../../ReusableComponents/Loader";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import ApiKey from "../ApiKeys/ApiKey";
import NoResults from "../../ReusableComponents/NoResults";
import CreateUpdateWebhookPopup from "./CreateUpdateWebhookPopup";
import {useWebhooksValues} from "../../logic-helpers/webhooks";
import Webhook from "./Webhook";

/**
 * SYNC with WebhookService::EVENTS in PHP
 * Type created in types.ts
 */
export const WebhookEventNames = [
    'cache.single',
    'cache.templates',
    'cache.all'
] as const;

export default function Webhooks() {

    const { loadAjax, webhooks } = useWebhooksValues()

    const [ isCreating, setIsCreating ] = useState(false);

    return <div className="setting-webhooks">
        <div className="title">
            Webhooks <button
            className="button small inactive"
            onClick={() => setIsCreating(true)}
        >Create <Plus /></button>
        </div>

        {
            loadAjax.status === 'loading' ?

                <Loader padding={100} /> :

                (webhooks.length > 0 ?
                        <Table>
                            <TableHead>
                                <TableHeadItem>URL</TableHeadItem>
                                <TableHeadItem>Events</TableHeadItem>
                                <TableHeadItem>Secret</TableHeadItem>
                                <div/>
                            </TableHead>
                            <Fragment>
                                {
                                    webhooks.map(webhook => <Webhook
                                        webhook={webhook}
                                        key={webhook.id}
                                    />)
                                }
                            </Fragment>
                        </Table>
                        :
                        <NoResults text="There are no Webhooks." />
                )
        }

        {
            isCreating && <CreateUpdateWebhookPopup onClose={() => setIsCreating(false)} />
        }

    </div>

}