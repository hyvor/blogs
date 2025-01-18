<script lang="ts">
	import IconArrowRight from '@hyvor/icons/IconArrowRight';

	const current = !old;
	interface Props {
		old: boolean;
		diff: boolean;
	}

	let { old, diff }: Props = $props();
</script>

<div class="wrap">
	{#if diff}
		<span class="before">
			{old ? 'Yes' : 'No'}
		</span>
		<IconArrowRight />
	{/if}

	<span class="after">
		{current ? 'Yes' : 'No'}
	</span>
</div>

<style>
	.wrap {
		display: flex;
		align-items: center;
	}
	.wrap :global(svg) {
		margin: 0 15px;
		color: var(--text-light);
	}
	.after {
		font-weight: 600;
	}
</style>
