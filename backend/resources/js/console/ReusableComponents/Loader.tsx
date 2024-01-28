import React from "react";

interface LoaderProps {
    size?: 'default' | 'small' | 'mini' | 'extra-mini',
    color?: string, 
    padding?: number,
    inline?: boolean
}

export default function Loader({
    size = 'default', 
    color = 'default',
    padding = 0,
    inline = false
} : LoaderProps) {

    const sizePx = {
        'extra-mini': 12,
        'mini': 14,
        'small': 20,
        'default': 26
    }[size];

    return <div
        className={`global-loader ${size}${inline ? ' inline' : ''}`}
        style={{ padding }}
    >
        <span 
            className="spinner" 
            style={{
                width: sizePx, 
                height: sizePx,
                borderColor: color ? color : undefined
            }}></span>
    </div>;

}

export function FullPageLoader({text} : {text?: string}) {

    return <div className="global-full-page-loader">
        <Loader />
        <div className="text">{text}</div>
    </div>

}