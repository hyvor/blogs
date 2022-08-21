import { useValues } from 'kea';
import React, { useState } from 'react';
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import userBlogsLogic from '../../logic/userBlogsLogic';
import getSubdomain from "../../logic-helpers/subdomain";
import Frequency from "./Frequency";
import Plan from "./Plan";
import {SubscriptionFrequency, SubscriptionPlan} from "../../types";

interface PlansProps {

    onSubscriptionCreate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any
    onSubscriptionUpdate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any,
    onSubscriptionCancel: () => any,

}

export default function Plans({onSubscriptionCreate, onSubscriptionCancel, onSubscriptionUpdate} : PlansProps) {

    const subdomain = getSubdomain();

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const [frequency, setFrequency] = useState
        (currentSubscription ? currentSubscription.frequency : 'monthly'); // monthly|yearly

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
                {
                    (['A', 'B', 'C', 'D', 'E'] as SubscriptionPlan[]).map(plan =>
                        <Plan
                            key={plan}
                            type={plan}
                            frequency={frequency}
                            onCreate={onSubscriptionCreate}
                            onUpdate={onSubscriptionUpdate}
                            onCancel={onSubscriptionCancel}
                        />
                    )
                }
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

    </div>;

}