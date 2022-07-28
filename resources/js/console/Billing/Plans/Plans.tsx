import { useValues } from 'kea';
import React, { useState } from 'react';
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import userBlogsLogic from '../../logic/userBlogsLogic';
import subscriptionLogic from '../../logic/subscriptionLogic';
import { FullPageLoader } from '../../ReusableComponents/Loader';
import getSubdomain from "../../logic-helpers/subdomain";
import Frequency from "./Frequency";
import Plan from "./Plan";

export default function Plans() {

    const subdomain = getSubdomain();

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const [frequency, setFrequency] = useState
        (currentSubscription ? currentSubscription.frequency : 'monthly'); // monthly|yearly

    const subscriptionLogicInst = subscriptionLogic({subdomain});
    const { createSubscriptionAjax, updateSubscriptionAjax, cancelSubscriptionAjax } = useValues(subscriptionLogicInst);

    return <div>
        <div className="section-title plans-title">
            <div className="title">Plans</div>
            <div className="frequency-selector">
                <Frequency frequency={frequency} setFrequency={setFrequency} type="monthly" name="Monthly" />
                <Frequency frequency={frequency} setFrequency={setFrequency} type="yearly" name="Yearly" />
            </div>
        </div>
        <div className="section-content">
            <div className="plans">
                <Plan frequency={frequency} type="A" />
                <Plan frequency={frequency} type="B" />
                <Plan frequency={frequency} type="C" />
                <Plan frequency={frequency} type="D" />
                <Plan frequency={frequency} type="E" />
            </div>
            <div className="section-desc">
                Prices are shown in USD, including all VAT charges <br/>
                <div>
                    <a href="/pricing" className="link" target="_blank">
                        <span>Pricing</span>
                        <span className="icon"><BoxArrowUpRight /></span>
                    </a>
                </div>
            </div>
        </div>

        {
            createSubscriptionAjax.status === 'loading' ||
            updateSubscriptionAjax.status === 'loading' ||
            cancelSubscriptionAjax.status === 'loading'
            ?
            <FullPageLoader /> : null
        }

    </div>;

}