<script lang="ts">
	import { SplitControl } from "@hyvor/design/components";
	import PostStatusTag from "../../../../PostStatusTag.svelte";
	import { postVariantStore } from "../../../../postStore";
</script>


<SplitControl>
    <span slot="label">Status</span>
    <PostStatusTag status={$postVariantStore.status} />
</SplitControl>