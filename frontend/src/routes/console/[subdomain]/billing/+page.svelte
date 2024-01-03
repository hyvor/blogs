<script>
	import BillingBox from './BillingBox.svelte';
import BillingInfo from './BillingInfo/BillingInfo.svelte';
	import Plans from "./Plans/Plans.svelte";

</script>
<div class="view">

    <div class="column">

        <BillingBox>
            <Plans />
        </BillingBox>

        <BillingBox title="Billing Info">
            <BillingInfo />
        </BillingBox>

    </div>

    <div class="column">

        <BillingBox title="Usage">
            
        </BillingBox>

        <BillingBox title="Payments">
            
        </BillingBox>

        <BillingBox title="Subscriptions">
            
        </BillingBox>

    </div>

</div>

<style>

    .view {
        height:100%;
        display: flex;
    }

    .column {
        display:flex;
        flex:1;
        flex-direction: column;
    }

</style>