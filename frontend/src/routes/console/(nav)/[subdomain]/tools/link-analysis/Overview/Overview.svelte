<script lang="ts">
	import { Button, toast } from '@hyvor/design/components';
	import { linkAnalysisChecks, startFullAnalysis, type Stats } from '../linkAnalysisActions';
	import Stat from './Stat.svelte';
	import { createEventDispatcher } from 'svelte';
	import Analyses from './Analyses.svelte';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();
	interface Props {
		stats: Stats;
	}

	let { stats }: Props = $props();

	const dispatch = createEventDispatcher();

	let isStartingNewAnalysis = $state(false);

	function handleStartNewAnalysis() {
		isStartingNewAnalysis = true;

		const toastId = toast.loading(i18n.t('console.tools.linkAnalysis.startingAnalysis'));

		startFullAnalysis()
			.then((check) => {
				toast.success(i18n.t('console.tools.linkAnalysis.analysisStarted'), { id: toastId });
				linkAnalysisChecks.update((checks) => {
					return [check, ...checks];
				});
			})
			.catch((e) => {
				toast.error(e.message || 'Failed to start new analysis.', { id: toastId });
			})
			.finally(() => {
				isStartingNewAnalysis = false;
			});
	}
</script>

<div class="stats">
	<div class="stats-top">
		<div class="stats-top-left">
			<div class="stats-title">{i18n.t('console.tools.linkAnalysis.stats')}</div>

			<div class="stats-note">
				{i18n.t('console.tools.linkAnalysis.statsNote')}
			</div>
		</div>

		<div class="stats-top-right">
			<Button size="small" on:click={() => dispatch('links')}
				>{i18n.t('console.tools.linkAnalysis.seeLinks')}</Button
			>
		</div>
	</div>

	<div class="stats-inner">
		<div class="stats-row">
			<Stat value={stats.counts.ok} type="ok" />
			<Stat value={stats.counts.broken} type="broken" />
			<Stat value={stats.counts.risky} type="risky" />
			<Stat value={stats.counts.redirect} type="redirect" />
			<Stat value={stats.counts.ignored} type="ignored" />
		</div>
	</div>

	<div class="stats analyses">
		<div class="stats-top">
			<div class="stats-top-left">
				<div class="stats-title">{i18n.t('console.tools.linkAnalysis.analyses')}</div>

				<div class="stats-note">
					{i18n.t('console.tools.linkAnalysis.analysesNote')}
				</div>
			</div>

			<div class="stats-top-right">
				<Button size="small" on:click={handleStartNewAnalysis} disabled={isStartingNewAnalysis}>
					{i18n.t('console.tools.linkAnalysis.startNewAnalysis')}
				</Button>
			</div>
		</div>

		<div class="analyses-table">
			<Analyses />
		</div>
	</div>
</div>

<style lang="scss">
	.stats {
		.stats-top {
			display: flex;
		}
		.stats-top-left {
			flex: 1;
		}

		.stats-title {
			font-size: 20px;
			font-weight: 600;
			margin-bottom: 4px;
		}
		.stats-inner {
			background-color: var(--input);
			border-radius: 20px;
			padding: 10px;
		}
		.stats-row {
			display: flex;
			justify-content: space-around;
		}
		.stats-note {
			color: var(--text-light);
			font-size: 14px;
			margin-bottom: 10px;
			max-width: 500px;
		}
	}

	.analyses {
		margin-top: 25px;
		.analyses-table {
			margin-top: 10px;
		}
	}
</style>
