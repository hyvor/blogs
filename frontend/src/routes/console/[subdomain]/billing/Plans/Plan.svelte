<script lang="ts">
	import UpdateSuccessToast from './Success/UpdateSuccessToast.svelte';
	import CancelSuccessToast from './Success/CancelSuccessToast.svelte';
	import { Button, Loader, Tag, confirm, toast } from "@hyvor/design/components";
    import type { SubscriptionFrequency, SubscriptionPlan } from "../../../lib/types";
	import { isManuallyUpgraded, subscriptionStore } from "../../../lib/stores/subscriptionStore";
	import { cancelSubscription, createSubscription, updateSubscription } from "../paddleActions";
	import CheckoutSuccessToast from "./Success/CheckoutSuccessToast.svelte";
	import { initPaddle as initPaddleBase } from "../paddle";
	import { forceCancelSubscription } from "../billingActions";
	import SwitchConfirm from "./Confirm/SwitchConfirm.svelte";

    export let name: SubscriptionPlan;
    export let frequency: SubscriptionFrequency;

    let checkoutLoading = false;

    function getPriceFromPlan(type: SubscriptionPlan) {
        return {
            starter: 9,
            growth: 19,
            premium: 49,
            team: 299,
            business: 699,
            enterprise: 1299
        }[type];
    }

    let price : number;
    let isCurrent = false;
    $: {
        price = getPriceFromPlan(name);
        if (frequency === 'yearly') price *= 10;

        isCurrent = $subscriptionStore?.plan === name &&
            $subscriptionStore?.frequency === frequency;
    }

    async function handleCancel() {

        if ($subscriptionStore?.status === 'deleted') {

            if (await confirm({
                title: 'Force Cancel Subscription',
                content: 'Are you sure you want to cancel the subscription now?',
                confirmText: 'Yes, cancel',
                cancelText: 'No, keep it',
                danger: true,
            })) {

                const toastId = toast.loading('Cancelling subscription...');

                forceCancelSubscription()
                    .then(() => {
                        toast.success('Subscription cancelled successfully.', {id: toastId});
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    })
                    .catch(e => {
                        toast.error(e.message, {id: toastId});
                    });

            }

        } else {

            if (await confirm({
                title: 'Cancel Subscription',
                content: 'Are you sure you want to cancel your subscription?',
                confirmText: 'Yes, cancel',
                cancelText: 'No, keep it',
                danger: true,
            })) {

                const toastId = toast.loading('Cancelling subscription...');

                cancelSubscription()
                    .then(() => {
                        toast.success(CancelSuccessToast, {
                            id: toastId,
                            duration: 12000,
                        });
                    })
                    .catch(e => {
                        toast.error(e.message, {id: toastId});
                    });

            }

        }

    }

    async function handleSwitch() {

        if (await confirm({
            title: 'Update Subscription',
            content: SwitchConfirm,
            contentProps: {
                name,
                frequency,
            },
            confirmText: 'Yes, update',
            cancelText: 'No, keep it',
        })) {

            const toastId = toast.loading('Updating subscription...');

            updateSubscription(name, frequency)
                .then(() => {
                    toast.success(UpdateSuccessToast, {
                        id: toastId,
                        duration: 12000,
                    });
                })
                .catch(e => {
                    toast.error(e.message);
                });

        }

    }

    async function initPaddle() {
        try {
            await initPaddleBase();
        } catch (e) {
            checkoutLoading = false;
            toast.error('Failed to load checkout. Please try again later.');
            return;
        }
    }

    async function handleUpgrade() {

        checkoutLoading = true;

        await initPaddle();

        createSubscription(name, frequency)
            .then(({link}) => {

                (window as any).Paddle.Checkout.open({
                    override: link,
                    loadCallback: () => {
                        checkoutLoading = false;
                    },
                    successCallback: handleUpgradeComplete,
                });

            })
            .catch(e => {
                checkoutLoading = false;
                toast.error(e.message);
            });

    }

    function handleUpgradeComplete() {

        const event = new CustomEvent('console:subscription:created', {
            detail: {
                price: getPriceFromPlan(name),
                plan: name,
                frequency,
            }
        });
        window.dispatchEvent(event);

        toast.success(CheckoutSuccessToast, {
            duration: 12000,
        });
    }

</script>

<div 
    class="plan"
    class:current={isCurrent}    
>

    <div class="plan-left">
        <span class="plan-name">{name}</span>

        {#if isCurrent}
            <Tag color="accent" size="x-small">CURRENT</Tag>
        {/if}
    </div>
    <div class="plan-right">
        <span class="plan-price">${ price }<span class="per-month">
            /{frequency === 'monthly' ? 'month' : 'year'}
        </span></span>
        <div class="plan-right-button-wrap">

            {#if isCurrent}
                <Button 
                    color="red" 
                    size="small"
                    on:click={handleCancel}
                >
                    Cancel
                </Button>
            {:else}
                {#if $subscriptionStore?.status !== 'deleted'}
                    <Button 
                        size="small"
                        disabled={isManuallyUpgraded()}
                        on:click={() => $subscriptionStore ? handleSwitch() : handleUpgrade()}
                    >
                        {$subscriptionStore ? "Switch" : "Upgrade"}
                    </Button>
                {/if}
            {/if}

        </div>
    </div>

</div>

{#if checkoutLoading}
    <div class="checkout-loading">
        <Loader 
            full 
            colorTrack="transparent" 
            color="white"
            size="large" 
        />
    </div>
{/if}

<style lang="scss">

    .checkout-loading {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .plan {
        padding: 15px;
        border-radius: 20px;
        background: var(--input);
        margin-bottom: 10px;
        display:flex;
        &.current {
            box-shadow: 0 0 2px 0px #886c6b;
        }
        .plan-left {
            flex:1;
            align-items: center;
            display: flex;
        }
        .plan-right {
            display: flex;
            align-items: center;
            .plan-right-button-wrap {
                min-width: 65px;
                text-align: center;
            }
        }

        .plan-name {
            font-size: 16px;
            font-weight: 600;
            text-transform: capitalize;
            margin-right: 5px;
        }
        .plan-price {
            font-size:14px;
            font-weight: 600;
            margin-right: 10px;
            .per-month {
                color: var(--text-light);
                font-weight: normal;
                font-size: 11px;
            }
        }
    }

</style>