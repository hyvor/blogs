import React from "react";
import {InfoCircle} from "react-bootstrap-icons";
import Tooltip from "./Tooltip";

export default function InfoTooltip(props: { children: React.ReactNode }) {

    return <Tooltip
        tooltip={props.children}
    >

        <span className="global-info-tooltip">
            <InfoCircle />
        </span>

    </Tooltip>

}