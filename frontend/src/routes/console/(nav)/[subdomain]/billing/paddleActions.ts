import { writable } from "svelte/store";
import consoleApi from "../../../lib/consoleApi";
import type { PaddlePayment, PaddleSubscriptionInfo, SubscriptionFrequency, SubscriptionPlan } from "../../../lib/types";
interface PaddleData {
    info: PaddleSubscriptionInfo | null,
    payments: PaddlePayment[]
}

export const paddleDataPromise = writable<Promise<PaddleData>>(
    Promise.resolve() as unknown as Promise<PaddleData>
);

export function loadPaddleData() {

    paddleDataPromise.set(consoleApi.get<PaddleData>({
        endpoint: '/billing/paddle'
    }));

}


export function createSubscription(plan: SubscriptionPlan, frequency: SubscriptionFrequency) {

    return consoleApi.post<{ redirect: string }>({
        endpoint: '/billing/subscription',
        data: {
            plan,
            is_annual: frequency === 'yearly'
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