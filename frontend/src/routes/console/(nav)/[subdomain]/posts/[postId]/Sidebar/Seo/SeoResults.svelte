<script lang="ts">
	import { variantSeoStore } from '../../../seoStore';
	import SeoScoreTag from './SeoScoreTag.svelte';
</script>

<div class="results">
	{#each $variantSeoStore.tests as test (test.name)}
		<div class="test">
			<div class="score-tag-wrap">
				<SeoScoreTag score={test.score} ignore={test.ignore} />
			</div>
			<div class="score-message">
				{test.message}
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
