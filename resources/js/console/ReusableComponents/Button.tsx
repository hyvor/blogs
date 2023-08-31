import React, { MouseEventHandler, ReactNode } from "react";
import Loader from "./Loader";

interface ButtonProps {
    children: ReactNode,
    onClick?: MouseEventHandler<HTMLButtonElement>,
    size?: 'big' | 'small' | 'mini' | 'tiny' | 'medium',
    type?: 'primary' | 'inactive' | 'text-only' | 'secondary' | 'danger' | 'light' | 'orange' | 'green' | 'gray',

    loading?: boolean,
}

export default function Button({
    size = 'medium',
    type = 'primary',
    loading = false,
    ...props
}: ButtonProps) {

    return <button 
        className={`button ${size} ${type}`} 
        onClick={props.onClick}
        disabled={loading}
    >
        {
            loading &&
                <span style={{marginRight: 5}}>
                    <Loader inline={true} size="mini" color="#fff" />
                </span>
        }
        {props.children}
    </button>

}