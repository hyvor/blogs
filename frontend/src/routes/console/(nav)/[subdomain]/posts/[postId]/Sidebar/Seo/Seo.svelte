<script>
	import UpgradeRequired from "../../../../billing/UpgradeRequired.svelte";
	import SeoAnalysis from "./SeoAnalysis.svelte";

</script>


<UpgradeRequired 
    minPlan="growth" 
    trialAllowed={true}
>

    <!-- @migration-task: migrate this slot by hand, `upgrade-text` is an invalid identifier -->
    <div slot="upgrade-text">
        The SEO analyzer is available on the <b>Growth plan</b> and above. Upgrade now to up your SEO game.
    </div>

    <SeoAnalysis />

</UpgradeRequired>