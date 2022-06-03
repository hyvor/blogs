import React, {Fragment, useState} from 'react';
import { useActions, useValues } from 'kea';
import subdomainLogic from '../../logic/subdomainLogic';
import redirectsLogic from '../../logic/redirectsLogic';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';
import Redirect from "./Redirect";
import {Table, TableHead, TableHeadItem } from "../../ReusableComponents/Table";
import TableLoadMore from "../../ReusableComponents/TableLoadMore";
import {Plus} from "react-bootstrap-icons";
import CreateUpdateRedirectPopup from "./CreateUpdateRedirectPopup";
import getSubdomain from "../../logic-helpers/subdomain";

export default function Redirects() {

    const redirectLogicBuilt = redirectsLogic({subdomain: getSubdomain()})
    redirectLogicBuilt.mount()
    const {
        redirects,
        loadAjax,
        hasMore,
        loadMoreAjax
    } = useValues(redirectLogicBuilt)
    const { loadMore } = useActions(redirectLogicBuilt)

    const [ isCreating, setIsCreating ] = useState<boolean>(false);

    return <div className="setting-redirects">
        <div className="title">
            Redirects <button
                className="button small inactive"
                onClick={() => setIsCreating(true)}
            >Create <Plus /></button>
        </div>

        {
            loadAjax.status === 'loading' ?

                <Loader padding={100} /> :

                (redirects.length > 0 ?
                    <Table>
                        <TableHead>
                            <TableHeadItem>Matching Path</TableHeadItem>
                            <TableHeadItem>Redirecting To</TableHeadItem>
                            <TableHeadItem>Type</TableHeadItem>
                            <div/>
                        </TableHead>
                        <Fragment>
                            {
                                redirects.map(redirect => <Redirect redirect={redirect} key={redirect.id} />)
                            }
                        </Fragment>
                        <TableLoadMore
                            hasMore={hasMore}
                            isLoading={loadMoreAjax.status === 'loading'}
                            onClick={loadMore}
                        />
                    </Table>
                :
                <NoResults
                    text="There are no redirects."
                    padding={40}
                    imageWidth={250}
                />
            )
        }

        {
            isCreating ? <CreateUpdateRedirectPopup onClose={() => setIsCreating(false)} /> : null
        }

    </div>
}