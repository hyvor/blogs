import React from "react";
import { ReactNode } from "react";
import { components } from "react-select";
import Select from "./Select";


interface SettingsSelectProps {
    name: string,
    value: string | number | null,
    options: {
        value: string | number;
        label: string | ReactNode
    }[],
    setPannel: Function,
}

function SettingsSelect({ name, value, options, setPannel }: SettingsSelectProps) {
    const SingleValue = (p: any) => {
        const value = p.data.label.props ? p.data.label.props.name : p.data.label;
        return <components.SingleValue {...p}>
            <div className='posts-filter-row'>
                <div className='posts-filter-name'>{name}</div>
                <div className='posts-filter-value'>{value}</div>
            </div>
        </components.SingleValue>
    };

    const valueCalculated = options.find(i => i.value === value) || options[0];

    return <Select
        value={valueCalculated}
        options={options}
        onChange={(v: any) => setPannel(v.value)}
        components={{ SingleValue }}
    />
}

export default SettingsSelect;