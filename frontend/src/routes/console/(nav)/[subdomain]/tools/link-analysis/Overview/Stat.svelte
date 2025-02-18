<script lang="ts">
	import type { LinkAnalysisStatusType } from '../../../../../lib/types';
	import LinkStatusTag from '../Links/LinkStatusTag.svelte';

	interface Props {
		/* export let name: string; */
		value: number;
		type: LinkAnalysisStatusType;
	}

	let { value, type }: Props = $props();
</script>

<div class="stat">
	<div class="stat-count">
		{value}
	</div>
	<div class="name-icon">
		<span class="icon">
			<LinkStatusTag {type} />
		</span>
	</div>
</div>

<style lang="scss">
	.stat {
		padding: 15px 25px;
		text-align: center;
		.stat-count {
			font-size: 25px;
			font-weight: 600;
			margin-bottom: 5px;
		}
	}
</style>
