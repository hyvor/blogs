import React, {ReactNode} from 'react'

export enum CalloutColors {
    BLUE = 'blue',
    ORANGE = 'orange',
    RED = 'red'
}

type CalloutProps = {
    icon?: ReactNode;
    color: CalloutColors | 'blue' | 'orange' | 'red';
    title?: string;
    text: ReactNode;
}

export default function Callout( {icon, color, title, text} : CalloutProps) {

    return <div className={"global-callout " + color}>
        {
            (icon || title) &&
            <div className="title">
                <span className="icon">{icon}</span>
                <span className="title-text">{title}</span>
            </div>
        }
        <div className="text">{text}</div>
    </div>;

}