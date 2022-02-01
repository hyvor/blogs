import { useValues } from 'kea';
import React, { useState } from 'react';
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import blogsLogic from '../logic/blogsLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import { FullPageLoader } from '../ReusableComponents/Loader';

export default function Plans({subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const [frequency, setFrequency] = useState
        (currentSubscription ? currentSubscription.frequency : 'monthly'); // monthly|yearly
    const [teamUsers, setTeamUsers] = useState(3); // team


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
                <Plan subdomain={subdomain} frequency={frequency} type="personal" name="Personal" />
                <Plan subdomain={subdomain} frequency={frequency} type="pro" name="Pro" />
                <Plan subdomain={subdomain} frequency={frequency} teamUsers={teamUsers} setTeamUsers={setTeamUsers} type="team" name="Team" />
                <Plan subdomain={subdomain} frequency={frequency} type="enterprise" name="Enterprise" />
            </div>
            <div className="section-desc">
                Prices are shown in USD, excluding VAT. <br/>
                <div>
                    <a href="/pricing" className="link" target="_blank">
                        <span>Pricing & Features</span>
                        <span className="icon"><BoxArrowUpRight /></span>
                    </a>
                </div>
            </div>
        </div>
    </div>;

}

function Frequency({type, name, frequency, setFrequency}) {
    return <span 
        className={type === frequency ? 'active' : ''}
        onClick={() => setFrequency(type)}
    >{name}</span>
}

function Plan({name, type, teamUsers, setTeamUsers, frequency, subdomain}) {

    const { findBlogBySubdomain } = useValues(blogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const { createSubscriptionAjax, createSubscription, updateSubscription, updateSubscriptionAjax } 
        = useValues(subscriptionLogic({subdomain}));

    function handleSubscriptionCreate() {

        createSubscription({
            plan: type,
            quantity: type === 'team' ? teamUsers : 1,
            frequency,
            onLoad: (payLink) => {
                Paddle.Checkout.open({
                    override: payLink
                });
            }
        });

    }

    let price = "Free";
    let buttonDisabled = false;
    let isCurrent = false;

    if (type === 'personal') {
        isCurrent = blog.subscribed === false;
    } else if (type === 'pro') {
        if (frequency === 'monthly') {
            price = <span className="no-monthly">No monthly plan</span>;
            buttonDisabled = true;
        } else {
            price = "$20";
        }
        isCurrent = currentSubscription?.plan === 'pro' && currentSubscription.frequency === frequency;
    } else if (type === 'team') {
        price = frequency === 'monthly' ? "$" + (8 * teamUsers) : "$" + (60 * teamUsers);

        isCurrent = currentSubscription?.plan === 'team' && 
            currentSubscription.frequency === frequency &&
            currentSubscription.quantity === teamUsers;

    } else if (type === 'enterprise') {
        price = frequency === 'monthly' ? "$800" : "$6000";

        isCurrent = currentSubscription?.plan === 'enterprise' && currentSubscription.frequency === frequency;
    }

    
    return <div className={"plan" + (isCurrent ? " current" : "")}>
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

            <div className="plan-right-button-wrap">
                {
                    isCurrent ? 
                    <span className="current-text">Current</span> :
                    <button 
                        className={"button small inactive" + (buttonDisabled ? " disabled" : "")}
                        onClick={handleSubscriptionCreate}
                    > {
                        currentSubscription ? "Switch" : "Upgrade"
                    } </button>
                }
            </div>
        </div>


        {
            createSubscriptionAjax.status === 'loading' ?
            <FullPageLoader /> : null
        }

    </div>
}