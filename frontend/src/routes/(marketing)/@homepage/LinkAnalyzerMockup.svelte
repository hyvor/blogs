<script lang="ts">
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconGearFill from '@hyvor/icons/IconGearFill';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconXLg from '@hyvor/icons/IconXLg';

	function toXY(angleDeg: number, radius: number) {
		const rad = (angleDeg * Math.PI) / 180;
		return { x: +(Math.cos(rad) * radius).toFixed(1), y: +(Math.sin(rad) * radius).toFixed(1) };
	}

	// links checked around a gear that never stops turning - one of them is broken
	const count = 5;
	const step = 360 / count;
	const links = Array.from({ length: count }, (_, i) => {
		const { x, y } = toXY(-90 + step * i, 108);
		return { broken: i === 3, x, y };
	});
</script>

<div class="link-check-mockup">
	<div class="orbit-wrap">
		<div class="gear-hero">
			<span class="gear-spin"><IconGearFill size={42} /></span>
		</div>

		{#each links as l}
			<div class="link-node" class:broken={l.broken} style="transform: translate({l.x}px, {l.y}px)">
				<IconLink45deg size={20} />
				<span class="status-badge">
					{#if l.broken}
						<IconXLg size={8} />
					{:else}
						<IconCheck size={9} />
					{/if}
				</span>
			</div>
		{/each}
	</div>
</div>

<style>
	.link-check-mockup {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 44px 20px;
	}

	.orbit-wrap {
		position: relative;
		width: 270px;
		height: 270px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.gear-hero {
		width: 100px;
		height: 100px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.gear-spin {
		display: flex;
		animation: spin 7s linear infinite;
	}

	@keyframes spin {
		to {
			transform: rotate(360deg);
		}
	}

	.link-node {
		position: absolute;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 52px;
		height: 52px;
		border-radius: 100px;
		color: var(--green-dark, var(--green));
		background: color-mix(in srgb, var(--green) 20%, var(--background));
		box-shadow: 0 4px 12px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.link-node.broken {
		color: var(--red-dark, var(--red));
		background: color-mix(in srgb, var(--red) 20%, var(--background));
	}

	.status-badge {
		position: absolute;
		right: -3px;
		bottom: -3px;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 18px;
		height: 18px;
		border-radius: 100px;
		color: #fff;
		background: var(--green-dark, var(--green));
		border: 2px solid var(--background);
	}

	.link-node.broken .status-badge {
		background: var(--red-dark, var(--red));
	}

	@media (max-width: 480px) {
		.orbit-wrap {
			width: 220px;
			height: 220px;
			transform: scale(0.85);
		}
	}
</style>
