import {SubscriptionFrequency, SubscriptionPlan} from "../enums";

export type Subscription = {
    status: SubscriptionStatus;
    quantity: number;

    plan: SubscriptionPlan,
    frequency:SubscriptionFrequency,

    created_at: number;
    ends_at: number | null;

    is_on_grace_period: boolean;
}

enum SubscriptionStatus {

    ACTIVE = 'active',
    PAST_DUE = 'past_due',
    PAUSED = 'paused',
    DELETED = 'deleted'

};