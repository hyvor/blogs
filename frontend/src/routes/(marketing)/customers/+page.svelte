<script>
	import FullTrialSignup from "../@components/FullTrialSignup.svelte";
	import Reviews from "../@components/Reviews.svelte";
import FeatureSectionTitle from "../@homepage/FeatureSectionTitle.svelte";
import CustomerList from "./CustomerList.svelte";
</script>

<FeatureSectionTitle 
    title="Customers"
    subtitle="See how our customers use Hyvor Blogs. Possibilities are endless!"
    wrapStyle="margin-top:50px"
/>

<div class="wrap">
    <CustomerList />
</div>

<FullTrialSignup />
<Reviews button={false} />

<style>
    .wrap {
        padding: 60px 0;
        width: 1200px;
        max-width: 100%;
        margin: auto;
    }
</style>