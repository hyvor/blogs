import { useValues } from 'kea';
import React from 'react'
import subscriptionLogic from '../logic/subscriptionLogic';
import Loader from '../ReusableComponents/Loader';
import NoResults from '../ReusableComponents/NoResults';

export default function SubscriptionHistory({subdomain}) {

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    return <div className="billing-history">
    { 
        loadAjax.status === 'loading' ?
        <Loader padding={60} /> :
        <div className="receipts">
            {
                data.subscriptions.length ?
                <div className="receipts-table">
                    <div className="receipts-row header">
                        <div>Status</div>
                        <div>Created on</div>
                        <div>Ended on</div>
                        <div>Plan</div>
                        <div>Frequency</div>
                    </div>
                    <div className="receipts-results-wrap">
                    {
                        data.subscriptions.map(subscription => {
                            return <div className="receipts-row">
                                <div>{ subscription.status }</div>
                                <div> <FriendlyDate time={subscription.created_at} /> </div>
                                <div> <FriendlyDate time={subscription.ends_at} /> </div>
                                <div>{ subscription.plan }</div>
                                <div>{ subscription.frequency }</div>
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