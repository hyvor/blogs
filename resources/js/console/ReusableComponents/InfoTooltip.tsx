import React from "react";
import {InfoCircle} from "react-bootstrap-icons";

export default function InfoTooltip(props: { children: React.ReactNode }) {

    return <span className="global-info-tooltip">

        <span className="tooltip-icon">
            <InfoCircle />
        </span>

        <span className="tooltip-content">
            {props.children}
        </span>

    </span>

}