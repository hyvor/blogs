import { useValues } from 'kea';
import React from 'react';
import blogsLogic from '../logic/blogsLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import dayjs from 'dayjs';
import Loader from '../ReusableComponents/Loader';

export default function SubscriptionDetails({subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);

    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    return null;
    return <div className="subscription-details">
        { 
            loadAjax.status === 'loading' ?
            <Loader padding={60} /> :
            <div>
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
                <div className="details-row">
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
                    </div>
                </div>
            </div>
        }
    </div>;

}
