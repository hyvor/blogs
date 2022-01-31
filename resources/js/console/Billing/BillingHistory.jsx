import { useValues } from 'kea';
import React from 'react'
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import subscriptionLogic from '../logic/subscriptionLogic';
import Loader from '../ReusableComponents/Loader';
import NoResults from '../ReusableComponents/NoResults';
import { FriendlyDate } from '../ReusableComponents/Time';

export default function BillingHistory({subdomain}) {

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    if (data.receipts && data.receipts.length) {
    for (var i = 0; i < 100; i++) {
        data.receipts.push(data.receipts[0]);
    }
}

    return <div className="billing-history">
        { 
            loadAjax.status === 'loading' ?
            <Loader padding={60} /> :
            <div className="receipts">
                {
                    data.receipts.length ?
                    <div className="receipts-table">
                        <div className="receipts-row header">
                            <div>Amount</div>
                            <div>Tax</div>
                            <div>Date</div>
                            <div>Receipt</div>
                        </div>
                        <div className="receipts-results-wrap">
                        {
                            data.receipts.map(receipt => {
                                return <div key={receipt.id} className="receipts-row">
                                    <div>${receipt.amount}</div>
                                    <div>${receipt.tax}</div>
                                    <div><FriendlyDate time={receipt.paid_at} /></div>
                                    <div>
                                        <a 
                                            className="link" 
                                            href={receipt.receipt_url}
                                            target="_blank"
                                        >Receipt
                                            <span className="icon">
                                                <BoxArrowUpRight />
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            })
                        } 
                        </div>
                    </div>:
                    <NoResults text="No previous payments" />
                }
            </div>
        }
    </div>;

}