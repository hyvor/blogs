import React from 'react'

export default function Spinner({size = 16}) {

    return <div className="global-spinner" style={{width:size, height:size}}>
        <div className="global-spinner-dot"></div>
        <div className="global-spinner-dot"></div>
        <div className="global-spinner-dot"></div>
        <div className="global-spinner-dot"></div>
        <div className="global-spinner-dot"></div>
        <div className="global-spinner-dot"></div>
    </div>

}