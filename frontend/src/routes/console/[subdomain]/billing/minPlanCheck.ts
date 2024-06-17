import { get } from 'svelte/store';
import { isInTrial, subscriptionStore } from '../../lib/stores/subscriptionStore';
import type { SubscriptionPlan } from '../../lib/types';

const allPlanTypes: SubscriptionPlan[] = [
	'starter',
	'growth',
	'premium',
	'team',
	'business',
	'enterprise'
];

export function minPlanCheck(minPlan: SubscriptionPlan, trialAllowed = false) {
    const inTrial = isInTrial();
    const $subscriptionStore = get(subscriptionStore);
	
    return (
        ($subscriptionStore &&
            allPlanTypes.indexOf($subscriptionStore.plan) >= allPlanTypes.indexOf(minPlan)) ||
            (trialAllowed && inTrial)
	);
}
