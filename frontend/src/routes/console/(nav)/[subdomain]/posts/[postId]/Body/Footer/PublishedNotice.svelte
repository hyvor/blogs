<script lang="ts">
	import { tick } from 'svelte';
	import {
		documentStore,
		postEditingPublished,
		postEditor,
		postVariantStore
	} from '../../../postStore';
	import Compare from '../Compare/Compare.svelte';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();

	async function handleEditing() {
		postEditingPublished.update((v) => !v);
		await tick();
		$postEditor.focus();
	}

	let compare = $state(false);
</script>

{#if $postVariantStore.status !== 'draft'}
	{@const scheduled = $postVariantStore.status === 'scheduled'}
	<div class="published-notice">
		<span class="notice-text">
			{#if $postEditingPublished}
				{scheduled
					? i18n.t('console.postEditor.publishedNotice.editingScheduled')
					: i18n.t('console.postEditor.publishedNotice.editing')}
			{:else}
				{scheduled
					? i18n.t('console.postEditor.publishedNotice.scheduled')
					: i18n.t('console.postEditor.publishedNotice.published')}
			{/if}
		</span>
		<button onclick={handleEditing}>
			{#if $postEditingPublished}
				{i18n.t('console.postEditor.publishedNotice.switchToView')}
			{:else}
				{i18n.t('console.postEditor.publishedNotice.switchToEditing')}
			{/if}
		</button>
		{#if $postEditingPublished}
			&nbsp;&nbsp;&middot;&nbsp;
			<button onclick={() => (compare = true)}
				>{i18n.t('console.postEditor.publishedNotice.compare')}</button
			>
		{/if}
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
