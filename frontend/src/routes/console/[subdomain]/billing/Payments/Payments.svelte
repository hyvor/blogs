<script lang="ts">
	import { Loader, Table, TableRow } from "@hyvor/design/components";
	import { paddleDataPromise } from "../paddleActions";
	import PaymentRow from "./PaymentRow.svelte";
</script>

{#await $paddleDataPromise}
    <Loader padding={60} block />
{:then res}

    {#if res.payments.length}

        <div class="table">

            <Table columns="1fr 1fr 1fr">

                <TableRow head>
                    <div>Amount</div>
                    <div>Date</div>
                    <div>Receipt</div>
                </TableRow>

                {#each res.payments as payment (payment.id)}
                    <PaymentRow {payment} />
                {/each}

            </Table>

        </div>


    {:else}
        <!-- TODO: ADD IconMessage -->
    {/if}

{:catch error}

    <!-- TODO: ADD IconMessage error -->
    
{/await}


<style>
    .table {
        text-align: center;
    }
</style>
