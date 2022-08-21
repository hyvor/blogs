import { useActions, useValues } from 'kea';
import React, { useState } from 'react';
import userBlogsLogic from '../logic/userBlogsLogic';
import paddleLogic from '../logic/billing/paddleLogic';
import dayjs from 'dayjs';
import Loader from '../ReusableComponents/Loader';
import { DayDiff, FriendlyDate } from '../ReusableComponents/Time';
import { BoxArrowUpRight, Clock, CreditCard2FrontFill, ExclamationCircle } from 'react-bootstrap-icons';
import Callout from '../ReusableComponents/Callout';
import NoResults from '../ReusableComponents/NoResults';
import { PopupConfirm } from '../ReusableComponents/Popup';
import {SubscriptionInfo} from "../types";
import getSubdomain from "../logic-helpers/subdomain";

export default function CurrentSubscription() {

    const subdomain = getSubdomain()
    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const subscriptionLogicInst = paddleLogic({subdomain});
    const { data, loadAjax } = useValues(subscriptionLogicInst);
    const { cancelSubscription } = useActions(subscriptionLogicInst)

    const trialDaysDiff = blog.trial_ends_at ? dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd') : 0;

    const [ downgradePopup, setDowngradePopup ] = useState(false);
    const [cancelPopup, setCancelPopup] = useState(false);

    function handleDowngradeNow() {
        setDowngradePopup(true);
    }
    function handleDowngradeNowReal() {
        setDowngradePopup(false);
        cancelSubscription({
            forced: true,
            onSuccess: () => location.reload()
        });
    }

    function handleCancel() {
        setCancelPopup(true);
    }
    function handleCancelReal() {
        setCancelPopup(false);

    }

    return <div>

        {
            downgradePopup ?
            <PopupConfirm 
                title="Downgrade Now"
                text="Are you sure you want to force downgrade now? You will need to create a new subscription if you need to access the features again."
                name="Downgrade"
                onClick={handleDowngradeNowReal}
                onCancel={() => setDowngradePopup(false)}
                buttonClass="danger"
            /> : null
        }

        {
            cancelPopup ?
            <PopupConfirm 
                title="Cancel Subscription"
                text="Are you sure you want to cancel the subscription? You will no longer be able to access the blog after the billing period."
                onClick={handleCancelReal}
                name="Cancel Subscription"
                buttonClass="danger"
                onCancel={() => setCancelPopup(false)}
            /> : null
        }

        <div className="section-title">
            Current Subscription
        </div>
        <div className="section-content">
            

            <div className="subscription-details">

                {
                    !blog.is_on_trial && !blog.subscription ?

                    <NoResults 
                        text="This blog does not have a subscription"
                        padding={40}
                        imageWidth={150}
                    /> :

                <div>

                    {
                        blog.is_on_trial && !blog.subscription ?
                        <Callout 
                            title="30-days trial"
                            icon={<Clock />}
                            text={
                                <div>
                                    This blog is currently on the 30-days trial period. The trial expires in <b>{trialDaysDiff} day{ trialDaysDiff === 1 ? "" : "s" }</b>.
                                </div>
                            }
                            color="blue"
                        />
                        : null
                    }

                    {
                        currentSubscription && currentSubscription.status === 'past_due' ?
                        <Callout 
                            title="Payment Issues"
                            icon={<ExclamationCircle />}
                            text={
                                <div>
                                    We were unable to process your last payment. This can happen for several reasons such as expired card details or insufficient funds. Please make sure the payment method below is up-to-date, and update it necessary.
                                </div>
                            }
                            color="orange"
                        />
                        : null
                    }

                    {
                        currentSubscription && currentSubscription.status === 'paused' ?
                        <Callout 
                            title="Subscription Paused"
                            icon={<ExclamationCircle />}
                            text={
                                <div>
                                    We tried several times, but we could not charge your card. Therefore, your subscription is now paused. Please update card details below to restart the subscription and continue using Hyvor Blogs.
                                </div>
                            }
                            color="orange"
                        />
                        : null
                    }

                    {
                        currentSubscription && currentSubscription.status === 'deleted' ?
                        <Callout
                            title="Subscription Cancelled"
                            icon={<ExclamationCircle />}
                            text={
                                <div>
                                    This subscription is now cancelled. You will have access to this plan's features until <b><FriendlyDate time={currentSubscription.ends_at} /></b>. Thereafter, this blog will be downgraded.
                                    <div style={{marginTop: 10}}>
                                        <button 
                                            className="button danger small"
                                            onClick={handleDowngradeNow}
                                        >Downgrade Now</button>
                                    </div>
                                </div>
                            }
                            color="red"
                        />
                        : null
                    }

                    {
                        currentSubscription ?
                        <div className="details-row">
                            <div className="details-card">
                                <div className="card-title">
                                    Plan
                                </div>
                                <div className="card-content">
                                    { currentSubscription.plan }
                                </div>
                            </div>
                            <div className="details-card">
                                <div className="card-title">
                                    Plan Status
                                </div>
                                <div className="card-content">
                                    <span className={"plan-status " + currentSubscription.status}>
                                        { currentSubscription.status }
                                    </span>
                                </div>
                            </div>
                        </div> : 
                        null
                    }

                    {
                        currentSubscription ?
                        (
                            loadAjax.status === 'loading' ?
                            <Loader padding={60} /> :
                            <InfoSection info={data.info} />
                        ) : null
                    }

                        {
                            currentSubscription && currentSubscription.status !== 'deleted' ?
                            <div className="cancel-view">
                                <button className="button danger" onClick={handleCancel}>Cancel Subscription</button>
                            </div>
                            : null
                        }

                </div>


                }
            </div>
        </div>
    </div>

}

function InfoSection({info} : {info: SubscriptionInfo}) {

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
                    <div>
                        <div className="card-detail-name">Card Ending</div>
                        <div>{ info.card_last_four }</div>
                    </div>
                    <div>
                        <div className="card-detail-name">Card Expiration</div>
                        <div>{ info.card_expiration }</div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

}