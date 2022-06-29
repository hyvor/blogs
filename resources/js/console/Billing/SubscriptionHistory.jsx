import { useValues } from 'kea';
import React from 'react'
import subscriptionLogic from '../logic/subscriptionLogic';
import Loader from '../ReusableComponents/Loader';
import NoResults from '../ReusableComponents/NoResults';
import { FriendlyDate } from '../ReusableComponents/Time';

export default function SubscriptionHistory({subdomain}) {

    const { data, loadAjax } = useValues(subscriptionLogic({subdomain}));

    return <div className="billing-history subscription-history">
    { 
        loadAjax.status === 'loading' ?
        <Loader padding={60} /> :
        <div className="receipts">
            {
                data.subscriptions.length ?
                <div className="receipts-table">
                    <div className="receipts-row header">
                        <div>Status</div>
                        <div>Created</div>
                        <div>Ended</div>
                        <div>Plan</div>
                        <div>Frequency</div>
                    </div>
                    <div className="receipts-results-wrap">
                    {
                        data.subscriptions.map(subscription => {
                            
                            let statusClass = subscription.status
                            let statusName = subscription.status;
                            if (subscription.status === 'past_due') {
                                statusClass = 'past-due'
                                statusName = 'Past due';
                            }

                            return <div className="receipts-row">
                                <div> <span className={"status-tag " + statusClass}>
                                        { statusName }
                                    </span>
                                </div>
                                <div> <FriendlyDate time={subscription.created_at} /> </div>
                                <div> {subscription.ends_at ? <FriendlyDate time={subscription.ends_at} /> : "-" } </div>
                                <div>{ subscription.plan }</div>
                                <div>{ subscription.frequency }</div>
                            </div>
                        })
                    } 
                    </div>
                </div>:
                <NoResults 
                    text="No subscriptions"
                    padding={40}
                    imageWidth={150}
                />
            }
        </div>
    }
</div>;

}