import { useActions, useValues } from 'kea';
import React, { useState } from 'react';
import blogsLogic from '../logic/blogsLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import dayjs from 'dayjs';
import Loader from '../ReusableComponents/Loader';
import { DayDiff, FriendlyDate } from '../ReusableComponents/Time';
import { BoxArrowUpRight, Clock, CreditCard2FrontFill, ExclamationCircle } from 'react-bootstrap-icons';
import Callout from '../ReusableComponents/Callout';
import NoResults from '../ReusableComponents/NoResults';
import { PopupConfirm } from '../ReusableComponents/Popup';

export default function CurrentSubscription({subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);

    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const subscriptionLogicInst = subscriptionLogic({subdomain});
    const { data, loadAjax } = useValues(subscriptionLogicInst);
    const { cancelSubscription } = useActions(subscriptionLogicInst)

    const trialDaysDiff =  dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd');

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
        cancelSubscription({
            onSuccess: () => location.reload()
        });
    }

    return <div>

        {
            downgradePopup ?
            <PopupConfirm 
                title="Downgrade Now"
                text="Are you sure you want to downgrade to the personal plan now? You cannot recover it later (You will need to pay for a subscription separately)."
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
                text="Are you sure you want to cancel the subscription? You will no longer have access to paid features."
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
                    !blog.is_on_trial && !blog.subscribed ?

                    <NoResults 
                        text="This blog does not have a subscription"
                        padding={40}
                        imageWidth={150}
                    /> :

                <div>

                    {
                        blog.is_on_trial ?
                        <Callout 
                            title="30-days trial"
                            icon={<Clock />}
                            text={
                                <div>
                                    This blog is currently on the 30-days trial period, which gives access to all the features. The trial expires in <b>{trialDaysDiff} day{ trialDaysDiff === 1 ? "" : "s" }</b>.
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
                                    This subscription is now cancelled. You will have access to this plan's features until <b><FriendlyDate time={currentSubscription.ends_at} /></b>. Thereafter, this blog will be downgraded to the Personal plan.
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
                            {
                                currentSubscription.plan === 'team' ?
                                <div className="details-card">
                                    <div className="card-title">
                                        Users
                                    </div>
                                    <div className="card-content">
                                        { currentSubscription.quantity }
                                    </div>
                                </div> : null
                            }
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

                    <div className="cancel-view">
                        {
                            currentSubscription.status !== 'deleted' ?
                            <button className="button danger" onClick={handleCancel}>Cancel Subscription</button>
                            : null
                        }
                    </div>

                </div>


                }
            </div>
        </div>
    </div>

}

function InfoSection({info}) {

    if (!info)
        return null;

    var paymentDaysDiffFromStartToEnd = dayjs.unix(info.next_payment_at).diff(
        dayjs.unix(info.last_payment_at),
        'd'
    );
    var paymentDaysDiffFromTodayToEnd = dayjs.unix(info.next_payment_at).diff(dayjs(), 'd');
    var paymentWidth = (100 - (paymentDaysDiffFromTodayToEnd / paymentDaysDiffFromStartToEnd * 100)) + "%";


    return <div>
        <div className="payment-cycle card">
            <div className="payment-top">
                <div className="payment-last">
                    <div className="payment-name">Last Payment</div>
                    <div className="payment-amount">${ info.last_payment }
                    </div>
                </div>
                <div className="payment-middle"></div>
                <div className="payment-next">
                    <div className="payment-name">Next Payment</div>
                    <div className="payment-amount">{  
                        info.next_payment ? `$${info.next_payment }` : "-" }</div>
                </div>
            </div>
            <div className="payment-bar">
                <div 
                    className="payment-bar-fill"
                    style={{width: paymentWidth}}
                ></div>
            </div>
            <div className="payment-top">
                <div className="payment-last">
                    <FriendlyDate time={info.last_payment_at} />
                </div>
                <div className="payment-middle">
                    {    
                    info.next_payment_at ?
                        <div className="payment-left-days">
                            Next payment in {paymentDaysDiffFromTodayToEnd} days
                        </div>
                        : null
                    }
                </div>
                <div className="payment-next">
                    {
                        info.next_payment ?
                        <FriendlyDate time={info.next_payment_at} /> : 
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