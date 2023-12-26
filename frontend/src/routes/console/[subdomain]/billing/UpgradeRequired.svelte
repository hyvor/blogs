<script lang="ts">
	import type { SubscriptionPlan } from "../../lib/types";


    const allPlanTypes : SubscriptionPlan[] = [
        'starter',
        'growth',
        'premium',
        'team',
        'business',
        'enterprise'
    ];

    export let minPlan : SubscriptionPlan;
    export let text : string;
    export let trialAllowed = false;

    

</script>