import React from "react";
import {JobStatus} from "../types";


export default function JobStatusBadge({ status } : { status: JobStatus }) {
    return <span className={"global-job-status-badge " + status}>
        {status}
    </span>
}