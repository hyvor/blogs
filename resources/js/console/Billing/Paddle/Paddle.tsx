import React, {Fragment, useState} from 'react';
import BillingColumn from "../Components/BillingColumn";
import BillingBox from "../Components/BillingBox";
import Plans from "../Plans/Plans";
import PaddleBillingInfo from "./PaddleBillingInfo";
import {ConsoleWindow, SubscriptionFrequency, SubscriptionPlan} from "../../types";
import paddleLogic from "../../logic/billing/paddleLogic";
import {useActions, useValues} from "kea";
import {FullPageLoader} from "../../ReusableComponents/Loader";
import {PopupNotice} from "../../ReusableComponents/Popup";
import getSubdomain from "../../logic-helpers/subdomain";
import { getPriceFromPlan } from '../Plans/Plan';

export default function Paddle() {

    const subdomain = getSubdomain();
    const paddleLogicInst = paddleLogic({subdomain});
    const { createSubscription, updateSubscription, cancelSubscription } = useActions(paddleLogicInst);
    const { createSubscriptionAjax, updateSubscriptionAjax, cancelSubscriptionAjax } = useValues(paddleLogicInst);

    const [checkoutLoading, setCheckoutLoading] = useState(false);
    const [checkoutSuccess, setCheckoutSuccess] = useState<false | 'update' | 'create'>(false);
    const [reloadCountdown, setReloadCountdown] = useState(10);

    function reportSubscriptionCreated(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {

        const w = window as any;

        const price = getPriceFromPlan(plan);

        if (w.uet_report_conversion) {
            w.uet_report_conversion(price.toString());
        }

        if (w.splitbee) {
            w.splitbee.track("Subscription Created", {
                plan,
                frequency
            })
        }

    }

    function handleCreate(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {

        createSubscription({
            plan,
            frequency,
            onLoad: (payLink: string) => {
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

                        reportSubscriptionCreated(plan, frequency);

                    }
                });
            }
        });
    }

    function handleUpdate(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {
        updateSubscription({
            plan,
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

    function handleCancel() {
        cancelSubscription({
            onSuccess: () => location.reload()
        });
    }

    return <Fragment>

        <BillingColumn>
            <BillingBox>
                <Plans
                    onSubscriptionCreate={handleCreate}
                    onSubscriptionUpdate={handleUpdate}
                    onSubscriptionCancel={handleCancel}
                />
            </BillingBox>
            <BillingBox>
                <PaddleBillingInfo />
            </BillingBox>
        </BillingColumn>

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
            createSubscriptionAjax.status === 'loading' ||
            updateSubscriptionAjax.status === 'loading' ||
            cancelSubscriptionAjax.status === 'loading'
                ?
                <FullPageLoader /> : null
        }

    </Fragment>

}