import { useValues } from 'kea';
import React, { useEffect, useState } from 'react';
import subscriptionLogic from '../logic/subscriptionLogic';
import byteFormatter from '../../helpers/byteFormatter';
import Loader from '../ReusableComponents/Loader';

export function Usage({subdomain}) {


    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    return loadAjax.status === 'loading' ?
        <Loader padding={60} /> :
        <div className="usage">
            <UsageBar 
                name="Users"
                data={data.usage.users}
            />
            <UsageBar 
                name="Media Storage"
                data={data.usage.media}
                bytes={true}
            />
            <div className="section-desc">
                Usage data is updated every 24 hours.
            </div>
        </div>

}

function UsageBar({name, data, bytes}) {

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
                <span className="usage-full">/ {total === 0 ? "∞" : total}</span>
            </div>
        </div>
        <div className="usage-bar-bar">
            <div className="usage-bar-fill" style={{width}}></div>
        </div>
    </div>
}
