import React, {Fragment, useEffect, useState} from "react";
import NoResults from "../../ReusableComponents/NoResults";
import {importLogic} from "../../logic/importLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions, useValues} from "kea";
import Loader from "../../ReusableComponents/Loader";
import {Table, TableHead, TableHeadItem, TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {Import} from "../../types";
import dayjs from "dayjs";
import JobStatusBadge from "../../ReusableComponents/JobStatusBadge";
import InfoTooltip from "../../ReusableComponents/InfoTooltip";
import {PopupNotice} from "../../ReusableComponents/Popup";
import DualSetting from "../../ReusableComponents/DualSetting";

export default function ImportHistory() {

    const logic = importLogic({subdomain: getSubdomain()});
    const { getImportsAjax, imports } = useValues(logic);
    const { getImports } = useActions(logic);

    useEffect(() => {
        getImports();
    }, []);

    return <div className="import-history">

        {
            getImportsAjax.status === 'loading' ?
                <Loader padding={40} /> :
                imports.length ?
                    <Table>
                        <TableHead>
                            <TableHeadItem>Name/URL</TableHeadItem>
                            <TableHeadItem>Type</TableHeadItem>
                            <TableHeadItem>Date</TableHeadItem>
                            <TableHeadItem>Status</TableHeadItem>
                            <TableHeadItem>Counts</TableHeadItem>
                        </TableHead>
                        <Fragment>
                            {
                                imports.map(e => <ImportRow data={e} key={e.id} />)
                            }
                        </Fragment>
                    </Table> :
                    <NoResults
                        text="No previous imports"
                        imageWidth={80}
                        padding={0}
                    />
        }
    </div>

}

function ImportRow({data} : {data: Import}) {

    const [isOpen, setIsOpen] = useState(false);

    return <div
        className="import-row"
        onClick={() => setIsOpen(true)}
    >
        <TableRow>
            <TableRowItem><span className="import-name">{ data.name }</span></TableRowItem>
            <TableRowItem>
                <span className="import-type">{ data.type }</span>
            </TableRowItem>
            <TableRowItem>{ dayjs.unix(data.created_at).fromNow() }</TableRowItem>
            <TableRowItem>
                <JobStatusBadge status={data.status} />
                {
                    data.status === "failed" &&
                    <InfoTooltip>
                        { data.error || 'Unknown error' }
                    </InfoTooltip>
                }
            </TableRowItem>
            <TableRowItem>
                { data.status === 'completed' ? data.imported_counts.posts + " posts" : '-' }
            </TableRowItem>
        </TableRow>

        {
            isOpen &&
            <PopupNotice
                title="Import details"
                text={
                    <div className="import-details">
                        <DualSetting
                            title="Name"
                            right={<span className="import-name">{data.name}</span>}
                        />
                        <DualSetting
                            title="Type"
                            right={<span className="import-type">{data.type}</span>}
                        />
                        <DualSetting title="Status" right={<JobStatusBadge status={data.status} />} />
                        <DualSetting
                            title="Started at"
                            right={dayjs.unix(data.created_at).format('DD/MM/YYYY HH:mm')}
                        />
                        <DualSetting
                            title="Options"
                            right={
                                <pre>{JSON.stringify(data.options, null, 2)}</pre>
                            }
                        />
                        <DualSetting
                            title="Imported Counts"
                            right={
                                <div className="imported-counts">
                                    <div>
                                        <span>Posts</span><span>{data.imported_counts.posts}</span>
                                    </div>
                                    <div>
                                        <span>Pages</span><span>{data.imported_counts.pages}</span>
                                    </div>
                                    <div>
                                        <span>Users</span><span>{data.imported_counts.users}</span>
                                    </div>
                                    <div>
                                        <span>Tags</span><span>{data.imported_counts.tags}</span>
                                    </div>
                                </div>
                            }
                        />
                    </div>
                }
                name="Close"
                onClick={(e: any) => {
                    e.stopPropagation();
                    setIsOpen(false)
                }}
            />
        }

    </div>

}