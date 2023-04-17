import { useValues } from 'kea';
import React, { useEffect, useState } from 'react';
import byteFormatter from '../../helpers/byteFormatter';
import Loader from '../ReusableComponents/Loader';
import getSubdomain from "../logic-helpers/subdomain";
import billingLogic from "../logic/billing/billingLogic";

export function Usage() {

    const { usage, loadAjax } = useValues(billingLogic({subdomain: getSubdomain()}));

    return loadAjax.status === 'loading' ?
        <Loader padding={60} /> :
        <div className="usage">
            <UsageBar 
                name="Users"
                data={usage.users}
            />
            <UsageBar 
                name="Media Storage"
                data={usage.media}
                bytes={true}
            />
            <UsageBar
                name="Auto-Translate Characters (this month)"
                data={usage.auto_translate}
                zero={true}
            />
           {/* <div className="section-desc">
                There may be a delay to update usage data
            </div>*/}
        </div>

}

function UsageBar({name, data, bytes = false, zero = false} : {name: string, data: any, bytes?: boolean, zero?: boolean}) {

    const [width, setWidth] = useState("0%");

    const calcWidth = data.percentage + "%";
    // animation
    useEffect(() => {
        setTimeout(() => {
            setWidth(calcWidth);
        }, 200);
    }, []);

    let current = data.current;
    let total = data.total;
    if (bytes) {
        current = byteFormatter(data.current);
        total = byteFormatter(data.total);
    }

    return <div className="usage-bar">
        <div className="usage-bar-top">
            <div className="usage-name">
                { name }
            </div>
            <div className="usage-number">
                <span className="usage-now">{current}</span>
                <span className="usage-full">/ {total === 0 && !zero ? "∞" : total}</span>
            </div>
        </div>
        <div className="usage-bar-bar">
            <div className="usage-bar-fill" style={{width}}></div>
        </div>
    </div>
}
