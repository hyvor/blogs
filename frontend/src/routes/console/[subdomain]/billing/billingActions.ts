import consoleApi from "../../lib/consoleApi";
import type { Subscription, UsageTypes } from "../../lib/types";

export function getBillingData() {
    return consoleApi.get<{
        usage: UsageTypes,
        subscriptions: Subscription[],
    }>({
        endpoint: '/billing'
    });
}

export function forceCancelSubscription() {
    return consoleApi.delete({
        endpoint: '/billing/subscription'
    });
}