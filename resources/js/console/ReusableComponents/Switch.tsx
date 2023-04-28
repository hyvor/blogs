import React from 'react'
import ReactSwitch from 'react-switch';

interface SwitchProps {

    checked: boolean,
    onChange: (
        checked: boolean,
        event: React.SyntheticEvent<MouseEvent | KeyboardEvent> | MouseEvent,
        id: string
    ) => void

}

export default function Switch(props: SwitchProps) {

    return <span data-testid="switch">
        <ReactSwitch 
            checkedIcon={false}
            uncheckedIcon={false}
            onColor="#896c6b"
            offColor="#777" // from colors.scss
            width={35}
            height={20}
            handleDiameter={21}
            boxShadow={"0 0 0px 1px " + (props.checked ? "#896c6b" : "#777")}
            activeBoxShadow="0 0 5px 1px #896c6b"
            {...props} />
    </span>
}