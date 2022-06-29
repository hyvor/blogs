import React, {Fragment, useState} from 'react';
import {Plus} from "react-bootstrap-icons";
import Loader from "../../ReusableComponents/Loader";
import {Table, TableHead, TableHeadItem} from "../../ReusableComponents/Table";
import NoResults from "../../ReusableComponents/NoResults";
import {useApiKeysValues} from "../../logic-helpers/api-keys";
import ApiKey from "./ApiKey";
import CreateApiKeyPopup from "./CreateApiKeyPopup";

export default function ApiKeys() {

    const { loadAjax, apiKeys } = useApiKeysValues()

    const [ isCreating, setIsCreating ] = useState(false);

    return <div className="setting-api-keys">
        <div className="title">
            API Keys <button
            className="button small inactive"
            onClick={() => setIsCreating(true)}
        >Create <Plus /></button>
        </div>

        {
            loadAjax.status === 'loading' ?

                <Loader padding={100} /> :

                (apiKeys.length > 0 ?
                        <Table>
                            <TableHead>
                                <TableHeadItem>Name</TableHeadItem>
                                <TableHeadItem>API</TableHeadItem>
                                <TableHeadItem>API Key</TableHeadItem>
                                <div/>
                            </TableHead>
                            <Fragment>
                                {
                                    apiKeys.map(apiKey => <ApiKey
                                            apiKey={apiKey}
                                            key={apiKey.id}
                                        />
                                    )
                                }
                            </Fragment>
                        </Table>
                        :
                        <NoResults text="There are no API Keys." />
                )
        }

        {
            isCreating && <CreateApiKeyPopup onClose={() => setIsCreating(false)} />
        }

    </div>

}