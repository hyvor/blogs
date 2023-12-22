import React, {ReactNode} from "react";

interface ChildrenProps {
    children: ReactNode,
    columns?: string
}

export function Table({ children, columns } : ChildrenProps) {
    return <div 
        className="global-table-view"
        style={{
            '--table-columns': columns
        } as React.CSSProperties}
    >{ children }</div>
}

export function TableHead({ children }: ChildrenProps) {
    return <div className="table-head">{ children }</div>
}

export function TableHeadItem({ children } : ChildrenProps) {
    return <div className="table-head-item">{ children }</div>
}

export function TableRow({ children }: ChildrenProps) {
    return <div className="table-row">{ children }</div>
}

export function TableRowItem({ children } : ChildrenProps) {
    return <div className="table-row-item">{ children }</div>
}