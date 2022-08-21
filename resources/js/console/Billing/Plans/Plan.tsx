import React, {useState} from "react";
import {PopupConfirm} from "../../ReusableComponents/Popup";
import {SubscriptionFrequency, SubscriptionPlan} from "../../types";
import {getUserBlogBlog} from "../../logic-helpers/blog";

interface PlanProps {
    type: SubscriptionPlan,
    frequency: SubscriptionFrequency,
    onCreate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any
    onUpdate: (plan: SubscriptionPlan, frequency: SubscriptionFrequency) => any,
    onCancel: () => any,
}

export default function Plan({type, frequency, onCreate, onUpdate, onCancel} : PlanProps) {

    const { subscription: currentSubscription } = getUserBlogBlog();

    const [updateConfirm, setUpdateConfirm] = useState(false);
    const [cancelConfirm, setCancelConfirm] = useState(false);

    function handleButton() {

        if (currentSubscription) {
            setUpdateConfirm(true);
        } else {
            onCreate(type, frequency)
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
                            onClick={() => setCancelConfirm(true)}
                        >Cancel</button> :
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
                    text="Are you sure you want to cancel the subscription? You will no longer be able to access the blog after the billing period."
                    onClick={onCancel}
                    name="Cancel Subscription"
                    buttonClass="danger"
                    onCancel={() => setCancelConfirm(false)}
                /> : null
        }

    </div>
}