import React from 'react'

export default function FormDualSetting({ left,  right }) {

    return <div className="global-form-dual-setting">

        <div className="dual-left">
            {left}
        </div>

        <div className="dual-right">
            { right }
        </div>

    </div>

}