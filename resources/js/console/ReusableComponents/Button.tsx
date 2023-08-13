import React, { MouseEventHandler, ReactNode } from "react";

interface ButtonProps {
    children: ReactNode,
    onClick?: MouseEventHandler<HTMLButtonElement>,
    size?: 'big' | 'small' | 'tiny' | 'medium',
    type?: 'primary' | 'inactive' | 'text-only' | 'secondary' | 'danger' | 'light' | 'orange' | 'green' | 'gray'
}

export default function Button({
    size = 'medium',
    type = 'primary',
    ...props
}: ButtonProps) {

    return <button className={`button ${size} ${type}`} onClick={props.onClick}>
        {props.children}
    </button>

}