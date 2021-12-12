import React from 'react'
import ReactSelect from 'react-select'

export default function Select(props) {
    return <ReactSelect 
        {...props} 
        classNamePrefix="react-select" 
        className={"react-select react-select-" + (props.type || 'normal')} 
    />
}