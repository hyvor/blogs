import { useActions, useValues } from 'kea';
import React, { useState } from 'react';
import { BoxArrowUpRight } from 'react-bootstrap-icons';
import { toast } from 'react-toastify';
import userBlogsLogic from '../logic/userBlogsLogic';
import subscriptionLogic from '../logic/subscriptionLogic';
import { FullPageLoader } from '../ReusableComponents/Loader';
import { PopupConfirm, PopupNotice } from '../ReusableComponents/Popup';

export default function Plans({subdomain}) {

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const [frequency, setFrequency] = useState
        (currentSubscription ? currentSubscription.frequency : 'monthly'); // monthly|yearly
    const [teamUsers, setTeamUsers] = useState(
        (currentSubscription && currentSubscription.plan === 'team' ? currentSubscription.quantity : 3)
    ); // team

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
                <Plan subdomain={subdomain} frequency={frequency} type="pro" name="Pro" />
                <Plan subdomain={subdomain} frequency={frequency} teamUsers={teamUsers} setTeamUsers={setTeamUsers} type="team" name="Team" />
                <Plan subdomain={subdomain} frequency={frequency} type="enterprise" name="Enterprise" />
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

function Frequency({type, name, frequency, setFrequency}) {
    return <span 
        className={type === frequency ? 'active' : ''}
        onClick={() => setFrequency(type)}
    >{name}</span>
}

function Plan({name, type, teamUsers, setTeamUsers, frequency, subdomain}) {

    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const subscriptionLogicInst = subscriptionLogic({subdomain});
    const { createSubscription, updateSubscription, cancelSubscription } = useActions(subscriptionLogicInst);

    const [checkoutLoading, setCheckoutLoading] = useState(false);
    const [checkoutSuccess, setCheckoutSuccess] = useState(false);
    const [reloadCountdown, setReloadCountdown] = useState(10);

    const [updateConfirm, setUpdateConfirm] = useState(false);

    function handleUpdate() {
        setUpdateConfirm(false);
        updateSubscription({
            plan: type,
            quantity: type === 'team' ? teamUsers : 1,
            frequency,
            onSuccess: () => {
                setCheckoutSuccess("update");
                setReloadCountdown(10);
                setInterval(() => {
                    setReloadCountdown(reloadCountdown => Math.max(0, reloadCountdown - 1));
                }, 1000);
            }
        });
    }

    function handleButton() {

        if (blog.subscribed) {

            setUpdateConfirm(true);
        
        } else {
            createSubscription({
                plan: type,
                quantity: type === 'team' ? teamUsers : 1,
                frequency,
                onLoad: (payLink) => {
                    setCheckoutLoading(true);
                    Paddle.Checkout.open({
                        override: payLink,
                        loadCallback: () => {
                            setCheckoutLoading(false);
                        },
                        successCallback: () => {
                            setCheckoutSuccess("create");
                            setReloadCountdown(10);
                            setInterval(() => {
                                setReloadCountdown(reloadCountdown => Math.max(0, reloadCountdown - 1));
                            }, 1000);
                        }
                    });
                }
            });
        }

    }

    let price = "";
    let buttonDisabled = false;
    let isCurrent = false;

    if (type === 'pro') {
        if (frequency === 'monthly') {
            price = <span className="no-monthly">No monthly plan</span>;
            buttonDisabled = true;
        } else {
            price = "$30";
        }
        isCurrent = blog.subscribed && currentSubscription?.plan === 'pro' && currentSubscription.frequency === frequency;
    } else if (type === 'team') {
        price = frequency === 'monthly' ? "$" + (8 * teamUsers) : "$" + (60 * teamUsers);

        isCurrent = blog.subscribed && currentSubscription?.plan === 'team' && 
            currentSubscription.frequency === frequency &&
            currentSubscription.quantity === teamUsers;

    } else if (type === 'enterprise') {
        price = frequency === 'monthly' ? "$800" : "$6000";

        isCurrent = blog.subscribed && currentSubscription?.plan === 'enterprise' && currentSubscription.frequency === frequency;
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
                    (
                        (currentSubscription && currentSubscription.is_on_grace_period)
                        ?
                        null :
                        <button 
                            className={"button small inactive" + (buttonDisabled ? " disabled" : "")}
                            onClick={handleButton}
                        > {blog.subscribed ? "Switch" : "Upgrade"} </button>
                    )

                }
            </div>
        </div>


        {
            checkoutLoading ?
            <FullPageLoader /> : null
        }

        {
            checkoutSuccess ? <PopupNotice 
                title={"Subscription successfully " + (checkoutSuccess === 'create' ? "created" : "updated")}
                text={<div>Your subscription was successfully {checkoutSuccess === 'create' ? "created" : "updated"}. It will take a few moments for changes to appear in the console. Please reload the page {reloadCountdown !== 0 ? `in ${reloadCountdown} seconds` : "now" }.</div>}
                name={reloadCountdown !== 0 ? `Reload in ${reloadCountdown} seconds` : "Reload"}
                buttonClass={reloadCountdown !== 0 ? "disabled inactive" : ""}
                onClick={() => location.reload()}
            /> : null
        }

        {
            updateConfirm ? <PopupConfirm
                title="Update Subscription"
                text={<div>
                    You are about to change your subscription plan to <b>{name} ({frequency})</b>{
                        type === 'team' ? 
                        (` with ${teamUsers} user` + (teamUsers === 1 ? "" : "s")) 
                        : ""
                    }. The price will be <b>prorated</b> and you will be charged now.
                </div>}
                name="Update"
                onClick={handleUpdate}
                onCancel={() => setUpdateConfirm(false)}
            /> : null
        }

    </div>
}