<script lang="ts">
	import IconStarFill from '@hyvor/icons/IconStarFill';
	import SealBase from './SealBase.svelte';

	const starAngles = Array.from({ length: 12 }, (_, i) => i * 30);
</script>

<SealBase>
	<div class="star-ring">
		{#each starAngles as angle}
			<span class="star" style="--angle: {angle}deg">
				<IconStarFill size={7} />
			</span>
		{/each}
	</div>
	<span class="label">GDPR</span>
</SealBase>

<style>
	.star-ring {
		position: absolute;
		inset: 0;
		--star-radius: 34px;
	}

	.star {
		position: absolute;
		top: 50%;
		left: 50%;
		width: 10px;
		height: 10px;
		margin: -5px 0 0 -5px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: #e3b34a;
		transform: rotate(var(--angle)) translateY(calc(var(--star-radius) * -1))
			rotate(calc(var(--angle) * -1));
	}

	.label {
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.03em;
		color: #fff;
		z-index: 1;
	}

	@media (max-width: 768px) {
		.star-ring {
			--star-radius: 28px;
		}
	}
</style>
