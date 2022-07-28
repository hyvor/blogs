import {useActions, useValues} from "kea";
import userBlogsLogic from "../../logic/userBlogsLogic";
import subscriptionLogic from "../../logic/subscriptionLogic";
import React, {useState} from "react";
import {FullPageLoader} from "../../ReusableComponents/Loader";
import {PopupConfirm, PopupNotice} from "../../ReusableComponents/Popup";
import {ConsoleWindow, SubscriptionFrequency, SubscriptionPlan} from "../../types";
import getSubdomain from "../../logic-helpers/subdomain";

interface PlanProps {
    type: SubscriptionPlan,
    frequency: SubscriptionFrequency,
}

export default function Plan({type, frequency} : PlanProps) {

    const subdomain = getSubdomain();
    const { findBlogBySubdomain } = useValues(userBlogsLogic);
    const { blog, blog: { subscription: currentSubscription} } = findBlogBySubdomain(subdomain);

    const subscriptionLogicInst = subscriptionLogic({subdomain});
    const { createSubscription, updateSubscription, cancelSubscription } = useActions(subscriptionLogicInst);

    const [checkoutLoading, setCheckoutLoading] = useState(false);
    const [checkoutSuccess, setCheckoutSuccess] = useState<false | 'update' | 'create'>(false);
    const [reloadCountdown, setReloadCountdown] = useState(10);

    const [updateConfirm, setUpdateConfirm] = useState(false);

    function handleUpdate() {
        setUpdateConfirm(false);
        updateSubscription({
            plan: type,
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

        if (currentSubscription) {
            setUpdateConfirm(true);
        } else {
            createSubscription({
                plan: type,
                frequency,
                onLoad: (payLink) => {
                    setCheckoutLoading(true);
                    (window as ConsoleWindow).Paddle.Checkout.open({
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

    let price = {
        A: 19,
        B: 49,
        C: 299,
        D: 699,
        E: 1299
    }[type];

    if (frequency === 'yearly') price *= 10;

    let buttonDisabled = false;
    let isCurrent = currentSubscription?.plan === type;

    return <div className={"plan" + (isCurrent ? " current" : "")}>
        <div className="plan-left">
            <span className="plan-name">Plan {type}</span>
        </div>
        <div className="plan-right">
            <span className="plan-price">${ price }<span className="per-month">
                /{frequency === 'monthly' ? 'month' : 'year'}
            </span></span>
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
                                > {currentSubscription ? "Switch" : "Upgrade"} </button>
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
                    You are about to change your subscription plan to <b>Plan {type} ({frequency})</b>. The price will be <b>prorated</b> and you will be charged now.
                </div>}
                name="Update"
                onClick={handleUpdate}
                onCancel={() => setUpdateConfirm(false)}
            /> : null
        }

    </div>
}