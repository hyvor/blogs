import React from "react";


export default function Loader({size = 'default', padding = 0} : {size?: 'default' | 'small' | 'mini', padding?: number}) {

    const sizePx = {
        'mini': 14,
        'small': 20,
        'default': 26
    }[size];

    return <div
        className={`global-loader ${size}`}
        style={{ padding }}
    >
        <span className="spinner" style={{width: sizePx, height: sizePx}}></span>
    </div>;

}

export function FullPageLoader({text} : {text?: string}) {

    return <div className="global-full-page-loader">
        <Loader />
        <div className="text">{text}</div>
    </div>

}