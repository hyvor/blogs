<script lang="ts">
	import type { SubscriptionFrequency, SubscriptionPlan } from "../../../../lib/types";

    export let name: SubscriptionPlan;
    export let frequency: SubscriptionFrequency;
</script>

Are you sure you want to switch your subscription to <b>{name} ({frequency === 'monthly' ? 'monthly' : 'yearly'})</b>? The price will be <b>prorated</b> and you will be charged now.