<script>
	import { IconClock } from "@hyvor/icons";
	import BlogStatus from "./BlogStatus.svelte";
	import { goto } from "$app/navigation";
	import { consoleUrlWithBlog } from "../../../../lib/consoleUrl";

    function handleUpgradeClick() {
        goto(consoleUrlWithBlog('/billing'));
    }
</script>


<BlogStatus
    icon={IconClock}
    title="Trial Ended"
    description="Your 7-days trial has ended. Upgrade to a paid plan to continue using your blog. If you have any questions, feel free to contact us."
    cta="Upgrade Now"
    on:click={handleUpgradeClick}
/>