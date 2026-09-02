<script lang="ts">
	import { documentStore, postVariantStore } from '../../../postStore';
	import Compare from '../Compare/Compare.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	let compare = $state(false);
</script>

{#if $postVariantStore.status !== 'draft'}
	{@const scheduled = $postVariantStore.status === 'scheduled'}
	<div class="published-notice">
		<span class="notice-text">
			{scheduled
				? i18n.t('console.postEditor.publishedNotice.scheduled')
				: i18n.t('console.postEditor.publishedNotice.published')}
		</span>
		<button onclick={() => (compare = true)}
			>{i18n.t('console.postEditor.publishedNotice.compare')}</button
		>
	</div>
{/if}

{#if compare}
	<Compare
		leftContent={$postVariantStore.content!}
		rightContent={$documentStore.checkpoint_content || $postVariantStore.content!}
		onclose={() => (compare = false)}
	/>
{/if}

<style>
	.published-notice {
		display: flex;
		align-items: center;
		padding-bottom: 5px;
		border-bottom: 1px solid var(--border);
		background-color: var(--blue-light);
		font-size: 14px;
		padding: 8px 30px;
	}
	.notice-text {
		color: var(--text-muted);
	}
	button {
		margin-left: 4px;
		text-decoration: underline;
	}
</style>
