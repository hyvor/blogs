<script lang="ts">
	import { SplitControl } from "@hyvor/design/components";
	import PostStatusTag from "../../../../PostStatusTag.svelte";
	import { postVariantStore } from "../../../../postStore";
</script>


<SplitControl>
    {#snippet label()}
		<span >Status</span>
	{/snippet}
    <PostStatusTag status={$postVariantStore.status} />
</SplitControl>