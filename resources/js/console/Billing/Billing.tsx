import React from 'react'
import BillingHistory from './BillingHistory';
import CurrentSubscription from './CurrentSubscription';
import Plans from './Plans/Plans';
import SubscriptionHistory from './SubscriptionHistory';
import { Usage } from './Usage';
import getSubdomain from "../logic-helpers/subdomain";

export default function Billing() {

    const subdomain = getSubdomain();

    return <div className="billing-view">
        <div className="billing-column">
            <div className="box billing-section">
                <Plans />
            </div>
            <div className="box billing-section">
                <CurrentSubscription subdomain={subdomain} />
            </div>
        </div>
        <div className="billing-column">
            <div className="box billing-section">
                <div className="section-title">
                    Usage
                </div>
                <div className="section-content">
                    <Usage subdomain={subdomain} />
                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Billing History
                </div>
                <div className="section-content">
                    <BillingHistory />
                </div>
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Subscription History
                </div>
                <div className="section-content">
                    <SubscriptionHistory subdomain={subdomain} />
                </div>
            </div>
        </div>
    </div>

}