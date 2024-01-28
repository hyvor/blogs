<script lang="ts">
	import { TableRow, Tag } from "@hyvor/design/components";
    import type { Subscription } from "../../../lib/types";
	import FriendlyDate from "../../../lib/components/date/FriendlyDate.svelte";

    export let subscription: Subscription;

    const statusColor = {
        active: 'green',
        deleted: 'red',
        past_due: 'orange',
    }[subscription.status] as 'green' | 'red' | 'orange';

    const statusName = subscription.status === 'past_due' ? 'Past due' : subscription.status;

</script>

<TableRow>

    <div>
        <Tag size="small" color={statusColor}>{statusName}</Tag>
    </div>
    <div> <FriendlyDate time={subscription.created_at} /> </div>
    <div> 
        {#if subscription.ends_at}
            <FriendlyDate time={subscription.ends_at} />
        {:else}
            -
        {/if}
    </div>
    <div>{ subscription.plan }</div>
    <div>{ subscription.frequency }</div>

</TableRow>

<style>
    div {
        text-transform: capitalize;
    }
</style>