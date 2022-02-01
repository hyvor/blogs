import { useValues } from 'kea';
import React from 'react'
import api from '../lib/api';
import subdomainLogic from '../logic/subdomainLogic';
import BillingHistory from './BillingHistory';
import CurrentSubscription from './CurrentSubscription';
import Plans from './Plans';
import SubscriptionHistory from './SubscriptionHistory';
import { Usage } from './Usage';

export default function Billing() {

    const { subdomain } = useValues(subdomainLogic);
    
    return <div className="billing-view">
        <div className="billing-column">
            <div className="box billing-section">
                <Plans subdomain={subdomain} />
            </div>
            <div className="box billing-section">
                <div className="section-title">
                    Current Subscription
                </div>
                <div className="section-content">
                    <CurrentSubscription subdomain={subdomain} />
                </div>
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
                    <BillingHistory subdomain={subdomain} />
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