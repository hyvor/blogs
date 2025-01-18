<script>
	import UpgradeRequired from "../../../../billing/UpgradeRequired.svelte";
	import LinkAnalysis from "./LinkAnalysis.svelte";
</script>


<UpgradeRequired 
    minPlan="growth"
    trialAllowed={true}
>

    <!-- @migration-task: migrate this slot by hand, `upgrade-text` is an invalid identifier -->
    <div slot="upgrade-text">
        Link Analysis is available on the <b>Growth plan</b> and above. Upgrade now to analyze links in your posts and say goodbye to broken links.
    </div>

    <LinkAnalysis />

</UpgradeRequired>