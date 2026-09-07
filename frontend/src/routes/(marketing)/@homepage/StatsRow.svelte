<script lang="ts">
	const stats = [
		{ value: '10,000+', label: 'Blogs & businesses', sub: 'and growing' },
		{ value: '99.9%', label: 'Uptime', sub: 'rock-solid reliability' },
		{ value: '2020', label: 'Founded', sub: 'independent & growing' }
	];
</script>

<section class="stats-row">
	<div class="hds-container inner">
		{#each stats as stat, i}
			{#if i > 0}
				<div class="divider"></div>
			{/if}
			<div class="stat">
				<div class="value">{stat.value}</div>
				<div class="label">{stat.label}</div>
				<div class="sub">{stat.sub}</div>
			</div>
		{/each}
	</div>
</section>

<style>
	.stats-row {
		padding: 72px 0;
	}

	.inner {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 0;
	}

	.stat {
		flex: 1;
		text-align: center;
		padding: 0 32px;
	}

	.value {
		font-size: clamp(40px, 5vw, 64px);
		font-weight: 800;
		letter-spacing: -0.03em;
		line-height: 1;
		color: var(--accent);
		margin-bottom: 8px;
	}

	.label {
		font-size: 1rem;
		font-weight: 600;
		color: var(--text);
		margin-bottom: 4px;
	}

	.sub {
		font-size: 13px;
		color: var(--text-light);
	}

	.divider {
		width: 1px;
		height: 64px;
		background: var(--border);
		flex-shrink: 0;
	}

	@media (max-width: 768px) {
		.inner {
			flex-wrap: wrap;
			gap: 40px;
		}
		.divider {
			display: none;
		}
		.stat {
			flex: 0 0 calc(50% - 20px);
			padding: 0;
		}
	}
</style>
