import { get, writable } from "svelte/store";
import type { UsageTypes, Subscription } from "../types";
import { blogStore } from "./blogStore";
import dayjs from "dayjs";

export const subscriptionStore = writable<Subscription | null>();
export const usageStore = writable<UsageTypes>();

export function isInTrial() {
    const blog = get(blogStore);
    return blog.trial_ends_at > dayjs().unix();
}


export function isManuallyUpgraded() {
    const subscription = get(subscriptionStore);
    return subscription && 
        subscription.paddle_subscription_id === null && 
        subscription.shopify_subscription_id === null;
}

export function hasTrialEndedAndNotSubscribed() {
    const blog = get(blogStore);
    const subscription = get(subscriptionStore);

    if (blog.type === 'dev')
        return false;

    return !isInTrial() && subscription === null;
}