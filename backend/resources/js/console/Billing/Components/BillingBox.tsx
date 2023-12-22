import React, {ReactNode} from 'react';

export default function BillingBox({children} : {children: ReactNode}) {
    return <div className="box billing-section">{children}</div>
}