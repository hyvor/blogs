<script>
	import { Button } from "@hyvor/design/components";
import CustomerList from "../../customers/CustomerList.svelte";
import FeatureSectionTitle from "../FeatureSectionTitle.svelte";
</script>

<FeatureSectionTitle
    title="Loved by Many & Highly Customizable!"
    subtitle="Hyvor Blogs is used by hundreds of bloggers around the world. Here are some of their blogs."
/>

<div class="wrap hds-container">
    <CustomerList max={6} />
</div>

<div class="action">
    <Button as="a" href="/customers" color="gray">
        View more customer blogs &rarr;
    </Button>
</div>

<style>
    .wrap {
        margin: 60px auto;
    }

    .action {
        margin-top: 50px;
        margin-bottom: 80px;
        text-align: center;
    }
</style>