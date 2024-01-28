import React, {ReactNode} from 'react';

export default function BillingColumn({children} : {children: ReactNode}) {
    return <div className="billing-column">{children}</div>
}