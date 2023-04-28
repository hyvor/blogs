import React from 'react'

interface DualSettingProps {
    left?: React.ReactNode,
    title: React.ReactNode,
    description?: React.ReactNode,
    right: React.ReactNode,

    column?: boolean
    subsection?: boolean,

    props?: object
}

export default function DualSetting({ 
    left, 
    title, 
    description, 
    right, 
    column = false, 
    subsection = false, 
    props = {} 
} : DualSettingProps) {

    return <div 
        className={
            "global-dual-setting" + 
            (column ? " column" : "") +
            (subsection ? " subsection" : "")
        } 
            
        {...props}>


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
