<script lang="ts">
	import { onMount } from 'svelte';
	import { Loader } from '@hyvor/design/components';
	import sudoApi from './lib/sudoApi';
	import { createChart } from './lib/chart';

	interface OverviewData {
		blogs: {
			total: number;
			total_30_days_change: number;
			blogs_with_custom_domains: number;
			by_month: Record<string, number>;
		};
	}

	let data: OverviewData | null = $state(null);
	let canvas: HTMLCanvasElement | undefined = $state(undefined);

	onMount(() => {
		sudoApi
			.get<OverviewData>({
				endpoint: '/overview'
			})
			.then((res) => {
				data = res;
				setTimeout(() => {
					if (canvas) {
						createChart(
							'Blogs',
							Object.keys(res.blogs.by_month),
							Object.values(res.blogs.by_month),
							canvas,
							'line'
						);
					}
				});
			});
	});
</script>

{#if data}
	<div class="stats-row">
		<div class="stat-card">
			<div class="stat-title">
				<a href="/sudo/blogs">Total Blogs</a>
			</div>
			<div class="stat-value">{data.blogs.total.toLocaleString()}</div>
			<div
				class="stat-change"
				class:positive={data.blogs.total_30_days_change >= 0}
				class:negative={data.blogs.total_30_days_change < 0}
			>
				{data.blogs.total_30_days_change >= 0
					? '+'
					: ''}{data.blogs.total_30_days_change.toLocaleString()}
				<span class="change-label">30d</span>
			</div>
		</div>

		<div class="stat-card">
			<div class="stat-title">Blogs with Custom Domains</div>
			<div class="stat-value">{data.blogs.blogs_with_custom_domains.toLocaleString()}</div>
		</div>
	</div>

	<div class="charts">
		<div class="chart-wrap">
			<div class="chart-title">Blogs</div>
			<div class="chart">
				<canvas bind:this={canvas}></canvas>
			</div>
		</div>
	</div>
{:else}
	<Loader full />
{/if}

<style>
	.stats-row {
		display: flex;
		gap: 1px;
		border-bottom: 1px solid var(--border);
		padding: 0 20px;
	}

	.stat-card {
		padding: 20px 32px;
		flex: 1;
		min-width: 200px;
	}

	.stat-title {
		font-size: 13px;
		color: var(--text-light);
		margin-bottom: 4px;
	}

	.stat-title a:hover {
		text-decoration: underline;
	}

	.stat-value {
		font-size: 28px;
		font-weight: 600;
	}

	.stat-change {
		font-size: 13px;
		margin-top: 2px;
	}

	.stat-change.positive {
		color: var(--green);
	}

	.stat-change.negative {
		color: var(--red);
	}

	.change-label {
		font-size: 11px;
		color: var(--text-light);
		margin-left: 2px;
	}

	.charts {
		padding: 30px;
		display: flex;
		gap: 20px;
		flex-wrap: wrap;
	}

	.chart-wrap {
		width: calc(50% - 20px);
		min-width: 450px;
	}

	.chart-title {
		font-weight: 600;
		font-size: 18px;
		margin-bottom: 20px;
	}
</style>
