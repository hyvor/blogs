<script lang="ts">
	import IconCheck from '@hyvor/icons/IconCheck';

	interface Props {
		features: string[];
	}

	let { features }: Props = $props();
</script>

<div class="features">
	{#each features as feature}
		<div class="feature">
			<IconCheck />
			<div>{@html feature}</div>
		</div>
	{/each}
</div>

<style>
	.features {
		margin-top: 15px;
	}
	.feature {
		display: flex;
		gap: 5px;
		margin-bottom: 5px;
		:global(svg) {
			margin-top: 3px;
			flex-shrink: 0;
		}
	}

	.feature :global(a) {
		color: var(--link);
		&:hover {
			text-decoration: underline;
		}
	}
</style>
