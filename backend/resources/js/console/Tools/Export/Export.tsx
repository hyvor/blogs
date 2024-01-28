import React, {Fragment, useState} from "react";
import DualSetting from "../../ReusableComponents/DualSetting";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import exportLogic from "../../logic/exportLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {useActions, useValues} from "kea";
import {toast} from "react-toastify";
import NoResults from "../../ReusableComponents/NoResults";
import {Table, TableHead, TableHeadItem, TableRow, TableRowItem} from "../../ReusableComponents/Table";
import {Export as ExportType} from "../../types";
import dayjs from "dayjs";
import JobStatusBadge from "../../ReusableComponents/JobStatusBadge";
import InfoTooltip from "../../ReusableComponents/InfoTooltip";


export default function Export() {

    const logic = exportLogic({subdomain: getSubdomain()});
    const { exports } = useValues(logic);
    const { getExports, exportNow } = useActions(logic);

    const [isExporting, setIsExporting] = useState(false);
    const [hasExportingStarted, setHasExportingStarted] = useState(false);

    function handleExport() {
        setHasExportingStarted(true);

        exportNow({
            onLoad: () => {
                setIsExporting(false);
                setHasExportingStarted(false);

                toast("Export started. You can track the progress in Export History.");
            },
            onError: () => {
                setHasExportingStarted(false);
            },
        })
    }

    return <div className="settings-export">

        <div className="title">
            Export
        </div>

        <DualSetting
            title="Export Data"
            description={
                <div>
                    Export your data in JSON format. See <a href="/docs/export" target="_blank">our docs</a> for more information.
                </div>
            }
            right={
                <button
                    className="button medium"
                    onClick={() => setIsExporting(true)}
                >Export Now</button>
            }
        />

        <DualSetting
            title="Export History"
            column={true}
            right={
                exports.length ?
                    <Table>
                        <TableHead>
                            <TableHeadItem>Format</TableHeadItem>
                            <TableHeadItem>Date</TableHeadItem>
                            <TableHeadItem>Status</TableHeadItem>
                            <TableHeadItem>File</TableHeadItem>
                        </TableHead>
                        <Fragment>
                            {
                                exports.map(e => <ExportRow data={e} key={e.id} />)
                            }
                        </Fragment>
                    </Table> :
                    <NoResults text="No previous exports." padding={40} imageWidth={100} />
            }
        />

        {
            isExporting &&
            <PopupConfirm
                title="Export Data"
                text="This can take a couple of minutes depending on the size of the export file. You can track the progress in Export History."
                name="Export Now"
                onClick={handleExport}
                onCancel={() => setIsExporting(false)}
                isLoading={hasExportingStarted}
            />
        }

    </div>

}


function ExportRow({data} : {data: ExportType}) {

    return <TableRow>

        <TableRowItem>{ "Hyvor Blogs JSON" }</TableRowItem>
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
            {
                data.url &&
                <a href={data.url} target="_blank" className="link">Download</a>
            }
        </TableRowItem>


    </TableRow>

}