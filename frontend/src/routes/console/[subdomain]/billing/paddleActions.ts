import consoleApi from "../../lib/consoleApi";
import type { SubscriptionFrequency, SubscriptionPlan } from "../../lib/types";


export function createSubscription(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {

    return consoleApi.post<{ link: string }>({
        endpoint: '/billing/paddle/subscription',
        data: {
            plan,
            frequency
        }
    })

}

export function updateSubscription(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {
    
    return consoleApi.patch({
        endpoint: '/billing/paddle/subscription',
        data: {
            plan,
            frequency
        }
    })
    
}

export function cancelSubscription() {

    return consoleApi.delete({
        endpoint: '/billing/paddle/subscription'
    })

}