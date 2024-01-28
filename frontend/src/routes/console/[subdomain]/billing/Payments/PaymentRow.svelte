<script lang="ts">
	import { Link, TableRow } from "@hyvor/design/components";
    import type { PaddlePayment } from "../../../lib/types";
	import FriendlyDate from "../../../lib/components/date/FriendlyDate.svelte";
	import { IconBoxArrowUpRight } from "@hyvor/icons";

    export let payment: PaddlePayment;
</script>

<TableRow>

    <div>{payment.amount} {payment.currency}</div>
    <div><FriendlyDate time={payment.paid_at} /></div>
    <div>
        <Link
            href={payment.receipt_url}
            target="_blank"
        >
            Receipt
            <IconBoxArrowUpRight slot="end" />
        </Link>
    </div>

</TableRow>