import { useActions, useValues } from 'kea';
import React from 'react';
import userBlogsLogic from '../../logic/userBlogsLogic';
import paddleLogic from '../../logic/billing/paddleLogic';
import dayjs from 'dayjs';
import Loader from '../../ReusableComponents/Loader';
import { FriendlyDate } from '../../ReusableComponents/Time';
import { Clock, CreditCard2FrontFill, ExclamationCircle } from 'react-bootstrap-icons';
import Callout from '../../ReusableComponents/Callout';
import NoResults from '../../ReusableComponents/NoResults';
import getSubdomain from "../../logic-helpers/subdomain";
import {PaddleSubscriptionInfo} from "../../types";

export default function PaddleBillingInfo() {

    const subdomain = getSubdomain()
    const subscriptionLogicInst = paddleLogic({subdomain});
    const { data, loadAjax } = useValues(subscriptionLogicInst);

    return <div>

        <div className="section-title">
            Billing Info
        </div>
        <div className="section-content">
            

            <div className="subscription-details">

                {
                    loadAjax.status === 'loading' ?
                        <Loader padding={60} /> :
                        (
                            data.info ?
                                <InfoSection info={data.info} /> :
                                <NoResults
                                    text="This blog does not have an active subscription"
                                    padding={40}
                                    imageWidth={150}
                                />
                        )
                }

            </div>
        </div>
    </div>

}

function InfoSection({info} : {info: PaddleSubscriptionInfo}) {

    if (!info)
        return null;

    const paymentDaysDiffFromStartToEnd = info.next_payment ? dayjs.unix(info.next_payment.at).diff(
        dayjs.unix(info.last_payment.at),
        'd'
    ) : 0;
    const paymentDaysDiffFromTodayToEnd = info.next_payment ? dayjs.unix(info.next_payment.at).diff(dayjs(), 'd') : 0;
    const paymentWidth = (100 - (paymentDaysDiffFromTodayToEnd / paymentDaysDiffFromStartToEnd * 100)) + "%";


    return <div>
        <div className="payment-cycle card">
            <div className="payment-top">
                <div className="payment-last">
                    <div className="payment-name">Last Payment</div>
                    <div className="payment-amount">{ info.last_payment.amount } { info.last_payment.currency }
                    </div>
                </div>
                <div className="payment-middle"/>
                <div className="payment-next">
                    <div className="payment-name">Next Payment</div>
                    <div className="payment-amount">{  
                        info.next_payment ? `${info.next_payment.amount } ${info.next_payment.currency}` : "-" }</div>
                </div>
            </div>
            <div className="payment-bar">
                <div
                    className="payment-bar-fill"
                    style={{width: paymentWidth}}
                />
            </div>
            <div className="payment-top">
                <div className="payment-last">
                    <FriendlyDate time={info.last_payment.at} />
                </div>
                <div className="payment-middle">
                    {    
                    info.next_payment ?
                        <div className="payment-left-days">
                            Next payment in {paymentDaysDiffFromTodayToEnd} days
                        </div>
                        : null
                    }
                </div>
                <div className="payment-next">
                    {
                        info.next_payment ?
                        <FriendlyDate time={info.next_payment.at} /> :
                        "-"
                    }
                </div>
            </div>

            
        </div>

        <div className="card">
            <div className="card-title">
                <div className="title-text">Payment Method</div>
                <a className="button small" target="_blank" href={info.update_url}>
                    Edit
                </a>
            </div>
            <div className="card-desc">
                <div className="card-icon">
                    <CreditCard2FrontFill size={150} />
                </div>
                <div className="card-details">
                    <div>
                        <div className="card-detail-name">Type</div>
                        <div>{ info.card_brand.toUpperCase() }</div>
                    </div>
                    {
                        info.card_last_four &&
                        <div>
                            <div className="card-detail-name">Card Ending</div>
                            <div>{ info.card_last_four }</div>
                        </div>
                    }
                    {
                        info.card_expiration &&
                        <div>
                            <div className="card-detail-name">Card Expiration</div>
                            <div>{ info.card_expiration }</div>
                        </div>
                    }
                </div>
            </div>
        </div>
    </div>  

}