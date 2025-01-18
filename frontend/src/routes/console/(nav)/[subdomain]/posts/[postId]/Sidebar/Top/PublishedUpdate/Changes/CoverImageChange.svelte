<script lang="ts">
	import IconArrowRight from '@hyvor/icons/IconArrowRight';

	interface Props {
		featuredImageOld: string | null;
		featuredImageNew: string | null;
		diff: boolean;
	}

	let { featuredImageOld, featuredImageNew, diff }: Props = $props();
</script>

<div class="wrap">
	{#if diff}
		<div class="before">
			{#if featuredImageOld}
				<img src={featuredImageOld} alt="old" />
			{:else}
				<span class="no">No image</span>
			{/if}
		</div>
		<div class="arrow">
			<IconArrowRight />
		</div>
	{/if}

	<div class="after">
		{#if featuredImageNew}
			<img src={featuredImageNew} alt="new" />
		{:else}
			<span class="no">No image</span>
		{/if}
	</div>
</div>

<style>
	.wrap {
		display: flex;
		align-items: center;
	}
	.before,
	.after {
		display: flex;
		align-items: center;
	}
	.no {
		color: var(--text-light);
		font-size: 14px;
	}
	.arrow {
		margin: 0 15px;
		color: var(--text-light);
		display: flex;
		align-items: center;
	}
	img {
		border-radius: 5px;
		max-width: 150px;
		max-height: 150px;
	}
</style>
