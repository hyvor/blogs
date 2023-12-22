import { useValues } from 'kea';
import React from 'react'
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import paddleLogic from '../../logic/billing/paddleLogic';
import Loader from '../../ReusableComponents/Loader';
import NoResults from '../../ReusableComponents/NoResults';
import { FriendlyDate } from '../../ReusableComponents/Time';
import getSubdomain from "../../logic-helpers/subdomain";

export default function PaddlePaymentHistory() {

    const subdomain = getSubdomain();
    const { data, loadAjax } = useValues(paddleLogic({subdomain}));

    return <div className="billing-history">
        { 
            loadAjax.status === 'loading' ?
            <Loader padding={60} /> :
            <div className="receipts">
                {
                    data.payments && data.payments.length ?
                    <div className="receipts-table">
                        <div className="receipts-row header">
                            <div>Amount</div>
                            <div>Date</div>
                            <div>Receipt</div>
                        </div>
                        <div className="receipts-results-wrap">
                        {
                            data.payments.map(payment => {
                                return <div key={payment.id} className="receipts-row">
                                    <div>{payment.amount} {payment.currency}</div>
                                    <div><FriendlyDate time={payment.paid_at} /></div>
                                    <div>
                                        <a 
                                            className="link" 
                                            href={payment.receipt_url}
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
                    <NoResults 
                        text="No previous payments"
                        padding={40}
                        imageWidth={150}
                    />
                }
            </div>
        }
    </div>;

}