<script lang="ts">
	import { Link } from '@hyvor/design/components';
	import { postVariantStore } from '../../../../postStore';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
</script>

Post published
<Link href={$postVariantStore.url} target="_blank">
	<span>View post</span>
	{#snippet end()}
		<IconBoxArrowUpRight size={10} />
	{/snippet}
</Link>

<style>
	span {
		font-size: 14px;
	}
</style>
