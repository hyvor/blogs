<script lang="ts">
	import { variantSeoStore } from '../../../seoStore';
	import SeoScoreTag from './SeoScoreTag.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	// seo-analyzer emits i18n keys (console.postEditor.seo.checks.*) rather than formatted text
	const t = (key: string, params?: Record<string, string | number>) => i18n.t(key as never, params);
</script>

<div class="results">
	{#each $variantSeoStore.tests as test (test.name)}
		<div class="test">
			<div class="score-tag-wrap">
				<SeoScoreTag score={test.score} ignore={test.ignore} />
			</div>
			<div class="score-message">
				{t(test.messageKey, test.messageParams)}
			</div>
		</div>
	{/each}
</div>

<style>
	.results {
		padding: 15px;
	}

	.test {
		display: flex;
		padding: 5px 0;
		align-items: flex-start;
	}

	.score-tag-wrap {
		text-align: right;
		flex-shrink: 0;
	}
	.score-message {
		margin-left: 8px;
		font-size: 14px;
		line-height: 18px;
		margin-top: 2px;
	}
</style>
