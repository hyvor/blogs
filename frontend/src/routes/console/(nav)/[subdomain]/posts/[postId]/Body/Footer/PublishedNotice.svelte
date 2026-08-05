<script lang="ts">
	import { tick } from 'svelte';
	import { postEditingPublished, postEditor, postVariantStore } from '../../../postStore';

	async function handleEditing() {
		postEditingPublished.update((v) => !v);
		await tick();
		$postEditor.focus();
	}
</script>

{#if $postVariantStore.status !== 'draft'}
	<div class="published-notice">
		<span class="notice-text">
			{#if $postEditingPublished}
				You are editing a published post.
			{:else}
				This post is published.
			{/if}
		</span>
		<button onclick={handleEditing}>
			{#if $postEditingPublished}
				Switch to published view
			{:else}
				Switch to editing mode
			{/if}
		</button>
	</div>
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
