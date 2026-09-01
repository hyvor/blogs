<script lang="ts">
	import { variantSeoStore } from '../../../seoStore';
	import SeoCurveSvg from './SeoCurveSvg.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
</script>

<div class="score-wrap">
	<div class="score">
		<div class="score-bar">
			<div class="placeholder">
				<SeoCurveSvg perc={100} />
			</div>
			<div class="score-circle">
				<SeoCurveSvg perc={$variantSeoStore.average} gradient={true} />
			</div>
		</div>
		<div class="score-value">
			<div class="score-name">{i18n.t('console.postEditor.seo.score')}</div>
			<div class="score-number">
				{Math.round($variantSeoStore.average)}%
			</div>
		</div>
	</div>
</div>

<style>
	.score-wrap {
		flex: 1;
		display: flex;
		justify-content: center;
		margin-bottom: 20px;
	}

	.score {
		width: 200px;
		height: 120px;
		position: relative;
		overflow: hidden;
		display: flex;
		align-items: flex-end;
		justify-content: center;
	}

	.score-bar {
		position: absolute;
		width: 100%;
		height: 200%;
		border-radius: 50%;
		top: 0;
	}
	.score-circle {
		position: absolute;
		top: 0;
	}

	.placeholder :global(circle) {
		stroke: #eee;
	}

	.score-value {
		margin-bottom: 5px;
		text-align: center;
	}
	.score-name {
		color: var(--text-light);
	}
	.score-number {
		font-size: 25px;
		font-weight: 600;
	}
</style>
