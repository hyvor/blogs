import { useValues } from 'kea';
import React, { useState } from 'react';
import {BoxArrowUpRight, Clock, ExclamationCircle} from 'react-bootstrap-icons';
import userBlogsLogic from '../../logic/userBlogsLogic';
import getSubdomain from "../../logic-helpers/subdomain";
import Frequency from "./Frequency";
import Plan from "./Plan";
import {SubscriptionFrequency, SubscriptionPlan} from "../../types";
import Callout from "../../ReusableComponents/Callout";
import dayjs from "dayjs";
import {FriendlyDate} from "../../ReusableComponents/Time";
import {isOnTrial} from "../../lib/blog-helpers";

interface PlansProps {

    onSubscriptionCreate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any
    onSubscriptionUpdate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any,
    onSubscriptionCancel: () => any,

}

export default function Plans({onSubscriptionCreate, onSubscriptionCancel, onSubscriptionUpdate} : PlansProps) {

    const subdomain = getSubdomain();

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const [frequency, setFrequency] = useState
        (currentSubscription ? currentSubscription.frequency : 'monthly'); // monthly|yearly

    const trialDaysDiff = blog.trial_ends_at ? dayjs.unix(blog.trial_ends_at).diff(dayjs(), 'd') : 0;

    return <div>
        <div className="section-title plans-title">
            <div className="title">Plans</div>
            <div className="frequency-selector">
                <Frequency frequency={frequency} setFrequency={setFrequency} type="monthly" name="Monthly" />
                <Frequency frequency={frequency} setFrequency={setFrequency} type="yearly" name="Yearly" />
            </div>
        </div>
        <div className="section-content">

            {
                isOnTrial(blog) && !blog.subscription &&
                <Callout
                    title="30-days trial"
                    icon={<Clock />}
                    text={
                        <div>
                            This blog is currently on the 30-days trial period. The trial expires in <b>{trialDaysDiff} day{ trialDaysDiff === 1 ? "" : "s" }</b>.
                        </div>
                    }
                    color="blue"
                />
            }

            {
                currentSubscription && currentSubscription.status === 'past_due' &&
                <Callout
                    title="Payment Issues"
                    icon={<ExclamationCircle />}
                    text={
                        <div>
                            We were unable to process your last payment. This can happen for several reasons such as expired card details or insufficient funds. Please make sure the payment method below is up-to-date, and update it if necessary.
                        </div>
                    }
                    color="orange"
                />
            }

            {
                currentSubscription && currentSubscription.status === 'deleted' &&
                <Callout
                    title="Subscription Cancelled"
                    icon={<ExclamationCircle />}
                    text={
                        <div>
                            This subscription is now cancelled. You will have access to this plan's features until <b><FriendlyDate time={currentSubscription.ends_at} /></b>. Thereafter, this blog will be downgraded.
                            {/*<div style={{marginTop: 10}}>
                                <button
                                    className="button danger small"
                                    onClick={handleDowngradeNow}
                                >Downgrade Now</button>
                            </div>*/}
                        </div>
                    }
                    color="red"
                />
            }

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