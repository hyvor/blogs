import React, { ReactNode } from "react";
import Button from "./Button";

interface Tab {
    label: ReactNode,
    value: string,
    icon?: ReactNode,
}

interface ButtonGroupProps {
    tabs: Tab[],
    active: string,
    setActive: (value: string) => void,
}


export default function Tabs({ tabs, active, setActive } : ButtonGroupProps) {

    return <div className="global-tabs">

        {
            tabs.map(tab => {

                return <div 
                    key={tab.value}
                    className={"tab" + (active === tab.value ? ' active' : '')}
                    onClick={() => setActive(tab.value)}
                >
                    {tab.icon && <span className="tab-icon">{tab.icon}</span>}
                    {tab.label}
                </div>

            })
        }

    </div>

}