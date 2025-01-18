<script>
	import UpgradeRequired from "../../../../billing/UpgradeRequired.svelte";
	import AiChat from "./AiChat.svelte";
</script>


<UpgradeRequired
    minPlan="growth" 
    trialAllowed={true}
>

    <!-- @migration-task: migrate this slot by hand, `upgrade-text` is an invalid identifier -->
    <div slot="upgrade-text">
        AI chat is available on the <b>Growth plan</b> and above. Upgrade now to use GPT to generate and improve your content.
    </div>

    <AiChat />

</UpgradeRequired>