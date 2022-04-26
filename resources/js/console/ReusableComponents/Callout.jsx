import React from 'react'

export default function Callout({icon, color, title, text}) {

    return <div className={"global-callout " + color}>
        <div className="title">
            <span className="icon">{icon}</span>
            <span className="title-text">{title}</span>
        </div>
        <div className="text">{text}</div>
    </div>

}