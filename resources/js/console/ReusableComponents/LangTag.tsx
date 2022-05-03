import React, {ReactNode} from "react";

type LangTagProps = {
    isActive?: boolean;
    onClick?: Function;
    code: string;
    icon: ReactNode | string
};

export default function LangTag({isActive, onClick, code, icon} : LangTagProps) {

    return <span
        className={"global-lang-tag" + (isActive ? " active" : "")}
        onClick={() => onClick && onClick()}
    >
        <span className="code">{code}</span>
        <span className="status-icon">
            { icon }
        </span>
    </span>

}