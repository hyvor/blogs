import { useValues } from 'kea';
import React from 'react';
import blogsLogic from '../logic/blogsLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import dayjs from 'dayjs';
import Loader from '../ReusableComponents/Loader';
import { DayDiff, FriendlyDate } from '../ReusableComponents/Time';
import { Clock, ExclamationCircle } from 'react-bootstrap-icons';
import Callout from '../ReusableComponents/Callout';

export default function CurrentSubscription({subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);

    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    const daysDiff =  dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd');

    return <div className="subscription-details">
        <div>

            {
                blog.is_on_trial ?
                <Callout 
                    title="30-days trial"
                    icon={<Clock />}
                    text={
                        <div>
                            This blog is currently on the 30-days trial period, which gives access to all the features. The trial expires in {daysDiff} day{ daysDiff === 1 ? "" : "s" }.
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

            <div className="details-row">
                <div className="details-card">
                    <div className="card-title">
                        Current Plan
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
            </div>
            {/* <div className="details-row">
                <div className="details-card">
                    <div className="card-title">
                        Last Payment
                    </div>
                    <div className="card-content">
                        <div>
                            { data.info.last_payment } USD
                        </div>
                        <div className="helper-text">
                            on { dayjs.unix(data.info.last_payment_at).format('MMM D, YYYY') }
                        </div>
                    </div>
                </div>
                <div className="details-card">
                    <div className="card-title">
                        Next Payment
                    </div>
                    <div className="card-content">
                        {
                            data.info.next_payment ?
                            <div>
                                <div>
                                    { data.info.next_payment } USD
                                </div>
                                <div className="helper-text">
                                    on { dayjs.unix(data.info.next_payment_at).format('MMM D, YYYY') }
                                </div>
                                <div className="helper-text">
                                    (in { dayjs.unix(data.info.next_payment_at).diff(dayjs(), 'd') } days)
                                </div>
                            </div>
                            : <div>-</div>
                        }
                    </div>
                </div> */}
        </div>
    </div>;

}
