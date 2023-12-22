import React from 'react'
import ReactSelect from 'react-select'
import { StateManagerProps } from "react-select/dist/declarations/src/useStateManager";

export interface SelectOption {
    value: any,
    label: any
}

type SelectProps = StateManagerProps & {
    type?: string | null
}

export default function Select(props: SelectProps) {
    return <ReactSelect
        {...props}
        classNamePrefix="react-select"
        className={"react-select react-select-" + (props.type || 'normal')}
    />
}