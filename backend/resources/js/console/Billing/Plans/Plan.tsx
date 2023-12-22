import React, {useState} from "react";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {SubscriptionFrequency, SubscriptionPlan} from "../../types";
import {getUserBlogBlog} from "../../logic-helpers/blog";
import {useActions} from "kea";
import billingLogic from "../../logic/billing/billingLogic";
import getSubdomain from "../../logic-helpers/subdomain";
import {isBlogManuallyUpgraded} from "../../lib/blog-helpers";

interface PlanProps {
    type: SubscriptionPlan,
    frequency: SubscriptionFrequency,
    onCreate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any
    onUpdate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any,
    onCancel: () => any,
}

export function getPriceFromPlan(type: SubscriptionPlan) {
    return {
        starter: 9,
        growth: 19,
        premium: 49,
        team: 299,
        business: 699,
        enterprise: 1299
    }[type];
}

export default function Plan({type, frequency, onCreate, onUpdate, onCancel} : PlanProps) {

    const subdomain = getSubdomain();
    const { subscription: currentSubscription } = getUserBlogBlog();

    const { forceCancel } = useActions(billingLogic({subdomain: getSubdomain()}))

    const [updateConfirm, setUpdateConfirm] = useState(false);
    const [cancelConfirm, setCancelConfirm] = useState(false);
    const [forceCancelConfirm, setForceCancelConfirm] = useState(false);

    function handleButton() {

        if (currentSubscription) {
            setUpdateConfirm(true);
        } else {
            onCreate(type, frequency)
        }

    }

    async function handleForceCancel() {
        forceCancel({
            onCancel: () => location.reload()
        });
    }

    let price = getPriceFromPlan(type);

    if (frequency === 'yearly') price *= 10;

    let buttonDisabled = false;
    let isCurrent = currentSubscription?.plan === type && currentSubscription?.frequency == frequency;

    const isCurrentSubscriptionCreatedWithoutPaddle = isBlogManuallyUpgraded(subdomain);

    return <div className={"plan" + (isCurrent ? " current" : "")}>
        <div className="plan-left">
            <span className="plan-name">{type}</span>
            { isCurrent && <span className="current-text">Current</span>}
        </div>
        <div className="plan-right">
            <span className="plan-price">${ price }<span className="per-month">
                /{frequency === 'monthly' ? 'month' : 'year'}
            </span></span>
            <div className="plan-right-button-wrap">
                {
                    isCurrent ?
                        <button
                            className="button small danger"
                            onClick={() => {
                                currentSubscription?.status === 'deleted' ?
                                    setForceCancelConfirm(true) :
                                    setCancelConfirm(true)
                            }}
                        >Cancel</button> :
                        (
                            currentSubscription?.status !== 'deleted' &&
                            !isCurrentSubscriptionCreatedWithoutPaddle &&
                            <button
                                className={"button small inactive" + (buttonDisabled ? " disabled" : "")}
                                onClick={handleButton}
                            > {currentSubscription ? "Switch" : "Upgrade"} </button>
                        )

                }
            </div>
        </div>


        {
            updateConfirm ? <PopupConfirm
                title="Update Subscription"
                text={<div>
                    You are about to change your subscription plan to <b>Plan {type} ({frequency})</b>. The price will be <b>prorated</b> and you will be charged now.
                </div>}
                name="Update"
                onClick={() => onUpdate(type, frequency)}
                onCancel={() => setUpdateConfirm(false)}
            /> : null
        }

        {
            cancelConfirm ?
                <PopupConfirm
                    title="Cancel Subscription"
                    text="Are you sure you want to cancel the subscription?"
                    onClick={onCancel}
                    name="Cancel Subscription"
                    buttonClass="danger"
                    onCancel={() => setCancelConfirm(false)}
                /> : null
        }

        {
            forceCancelConfirm &&
            <PopupConfirm
                title="Force Cancel Subscription"
                text="Are you sure you want to force cancel the subscription now?"
                onClick={handleForceCancel}
                name="Cancel Subscription"
                buttonClass="danger"
                onCancel={() => setForceCancelConfirm(false)}
            />
        }

    </div>
}