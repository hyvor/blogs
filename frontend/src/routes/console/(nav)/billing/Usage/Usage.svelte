<script lang="ts">
	import UsageBar from "./UsageBar.svelte";
    import {usageStore} from "../../../lib/stores/subscriptionStore";
</script>

<div class="usage">
    <UsageBar
        name="Users"
        data={$usageStore.users}
    />
    <UsageBar 
        name="Media Storage"
        data={$usageStore.media}
        bytes={true}
    />
    <UsageBar
        name="Auto-Translate Characters (this month)"
        data={$usageStore.auto_translate}
        zero={true}
    />
    <UsageBar
        name="GPT Tokens (this month)"
        data={$usageStore.gpt}
        zero={true}
    />
</div>
