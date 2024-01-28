<script lang="ts">
	import { Callout } from "@hyvor/design/components";
    import { isInTrial, isManuallyUpgraded, subscriptionStore } from "../../../lib/stores/subscriptionStore";
	import { IconClock, IconExclamationCircle, IconLightbulb } from "@hyvor/icons";
	import FriendlyDate from "../../../lib/components/date/FriendlyDate.svelte";

    const trialDays = 5 as number;

</script>

<div class="wrap">

<!-- In Trial -->
{#if !$subscriptionStore && isInTrial()}
    <Callout type="info">
        <div slot="title">Trial in Progress</div>
        <IconClock slot="icon" />
        Trial ends in <b>{trialDays} day{trialDays !== 1 ? "s" : ""}</b>. Upgrade now to continue using your blog.
    </Callout>
{/if}

<!-- Trial Ended -->
{#if !$subscriptionStore && !isInTrial()}
    <Callout type="warning">
        <div slot="title">Trial Ended</div>
        <IconExclamationCircle slot="icon" />
        Your trial has ended. Upgrade now to continue using your blog.
    </Callout>
{/if}

<!-- Manually Upgraded -->
{#if isManuallyUpgraded()}
    <Callout type="info">
        <div slot="title">Manually Upgraded</div>
        <IconLightbulb slot="icon" />
        Your blog was manually upgraded to the current plan by our team or through an offer. You will not be charged for this plan. If you wish to change your plan, please cancel the plan and upgrade to the desired plan (you will be charged).
    </Callout>
{/if}

<!-- Past Due -->
{#if $subscriptionStore && $subscriptionStore.status === 'past_due'}
    <Callout type="warning">
        <div slot="title">Payment Past Due</div>
        <IconExclamationCircle slot="icon" />
        We were unable to process your last payment. This can happen for several reasons such as expired card details or insufficient funds. Please make sure the payment method below is up-to-date, and update it if necessary.
    </Callout>
{/if}

<!-- Canceled -->
{#if $subscriptionStore && $subscriptionStore.status === 'deleted'}
    <Callout type="danger">
        <div slot="title">Subscription Canceled</div>
        <IconExclamationCircle slot="icon" />
        This subscription is now cancelled. You will have access to this plan's features until <b>
            {#if $subscriptionStore.ends_at}
                <FriendlyDate time={$subscriptionStore.ends_at} />
            {/if}
        </b>. Thereafter, this blog will be downgraded.
    </Callout>
{/if}

</div>

<style>
    .wrap:not(:empty) {
        margin-bottom: 15px;
    }
</style>