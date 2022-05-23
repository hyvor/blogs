import React from 'react'

interface SpinnerProps {
    size?: number,
    dark?: boolean
}

export default function Spinner({size = 16, dark = false} : SpinnerProps) {

    return <div
        className={"global-spinner" + (dark ? " dark" : "")}
        style={{width:size, height:size}}
    >
        <div className="global-spinner-dot" />
        <div className="global-spinner-dot" />
        <div className="global-spinner-dot" />
        <div className="global-spinner-dot" />
        <div className="global-spinner-dot" />
        <div className="global-spinner-dot" />
    </div>

}