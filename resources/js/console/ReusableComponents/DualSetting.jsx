import React from 'react'

export default function DualSetting({ left, title, description, right }) {

    return <div className="global-dual-setting">

        <div className="dual-left">
            { left }
            <div className="dual-title">{ title } </div>
            <div className="dual-description">{ description }</div>
        </div>

        <div className="dual-right">
            { right }
        </div>

    </div>

}