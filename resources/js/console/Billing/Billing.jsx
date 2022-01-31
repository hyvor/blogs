import dayjs from 'dayjs';
import { useValues } from 'kea';
import React, { useState } from 'react'
import api from '../lib/api';
import blogsLogic from '../logic/blogsLogic';
import subdomainLogic from '../logic/subdomainLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import Loader from '../ReusableComponents/Loader';

export default function Billing() {

    const { subdomain } = useValues(subdomainLogic);

    const [frequency, setFrequency] = useState('monthly'); // monthly|yearly
    const [teamUsers, setTeamUsers] = useState(3); // team

    function handleSubscriptionCreate() {

        api.post(subdomain, '/subscription')
            .then(function (json) {
                Paddle.Checkout.open({
                    override: json.payLink
                });
            });

    }

    function Plan({name, type}) {

        let posts = "Unlimited";
        let price = "Free";
        let buttonDisabled = false;

        if (type === 'personal') {
            posts = 100;
        } else if (type === 'personal_pro') {
            if (frequency === 'monthly') {
                price = "-";
                buttonDisabled = true;
            } else {
                price = "$20";
            }
        } else if (type === 'team') {
            price = frequency === 'monthly' ? "$" + (8 * teamUsers) : "$" + (60 * teamUsers);
        } else if (type === 'enterprise') {
            price = frequency === 'monthly' ? "$800" : "$6000";
        }

        
        return <div className="plan">
            <div className="plan-left">
                <span className="plan-name">{name}</span>
                {
                    type === 'team' ?
                    <span className="team-users-selector">
                        <span className="count">{ teamUsers + " users" }</span>
                        <span className="minus" onClick={() => setTeamUsers(Math.max(3, teamUsers - 1))}>-</span>
                        <span className="plus" onClick={() => setTeamUsers(Math.min(99, teamUsers + 1))}>+</span>
                    </span> : null
                }
            </div>
            <div className="plan-right">
                <span className="plan-price">{ price }</span>
                <button 
                    className={"button small inactive" + (buttonDisabled ? " disabled" : "")}
                    onClick={handleSubscriptionCreate}
                >Switch</button>
            </div>
        </div>
    }

    function Frequency({type, name}) {
        return <span 
            className={type === frequency ? 'active' : ''}
            onClick={() => setFrequency(type)}
        >{name}</span>
    }

    return <div className="billing-view">
        <div className="billing-column">
            <div className="box billing-section">
                <div className="section-title plans-title">
                    <div className="title">Plans</div>
                    <div className="frequency-selector">
                        <Frequency type="monthly" name="Monthly" />
                        <Frequency type="yearly" name="Yearly" />
                    </div>
                </div>
                <div className="section-content">
                    <div className="plans">
                        <Plan type="personal_pro" name="Personal Pro" />
                        <Plan type="team" name="Team" />
                        <Plan type="enterprise" name="Enterprise" />
                    </div>
                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Subscription Details
                </div>
                <div className="section-content">
                    <SubscriptionDetails subdomain={subdomain} />
                </div>
            </div>
        </div>
        <div className="billing-column">
            <div className="box billing-section">
                <div className="section-title">
                    Usage
                </div>
                <div className="section-content">

                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Billing History
                </div>
                <div className="section-content">

                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Subscription History
                </div>
                <div className="section-content">

                </div>
            </div>
        </div>
    </div>

}

function SubscriptionDetails({subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);

    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    return <div className="subscription-details">
        { 
            loadAjax.status === 'loading' ?
            <Loader style={{padding:40, textAlign: 'center'}} /> :
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