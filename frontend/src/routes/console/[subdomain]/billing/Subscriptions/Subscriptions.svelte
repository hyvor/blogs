<script lang="ts">
	import { IconMessage, Loader, Table, TableRow } from "@hyvor/design/components";
import { usageStore } from "../../../lib/stores/subscriptionStore";
	import { getBillingData } from "../billingActions";
	import SubscriptionRow from "./SubscriptionRow.svelte";

    const promise = getBillingData();

    promise.then(res => {
        // update usage store
        usageStore.set(res.usage);
    });

</script>

{#await promise}
    <Loader padding={60} block />
{:then res}

    {#if res.subscriptions.length}

        <div class="table">
            <Table columns="1fr 1fr 1fr 1fr 1fr">

                <TableRow head>
                    <div>Status</div>
                    <div>Created</div>
                    <div>Ended</div>
                    <div>Plan</div>
                    <div>Frequency</div>
                </TableRow>

                {#each res.subscriptions as subscription (subscription.id)}
                    <SubscriptionRow {subscription} />
                {/each}

            </Table>
        </div>


    {:else}
        <IconMessage empty message="No subscriptions" padding={60} />
    {/if}

{:catch}
    <IconMessage error padding={60} />
{/await}


<style>
    .table {
        text-align: center;
    }
</style>