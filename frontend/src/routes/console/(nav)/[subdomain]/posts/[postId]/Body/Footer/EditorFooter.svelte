<script lang="ts">
	import { postSuggestionModeStore } from '../../../postStore';
	import AutoTranslate from '../Editor/EditorTop/AutoTranslate/AutoTranslate.svelte';
	import PublishedNotice from './PublishedNotice.svelte';
	import SaveStatus from './SaveStatus.svelte';
	import SuggestionModeToggle from './SuggestionModeToggle.svelte';
	import { cant } from '../../../../../../lib/scope.svelte';
</script>

<div class="editor-footer" class:suggesting={$postSuggestionModeStore === 'suggesting'}>
	{#if cant('posts.write')}
		<div class="read-only-banner">
			You do not have permission to edit this post. (Read-only mode)
		</div>
	{/if}
	<PublishedNotice />
	<div class="footer-bottom">
		<div class="footer-left">
			<SuggestionModeToggle />
			<AutoTranslate />
		</div>

		<div class="footer-right">
			<!-- see plugin-wordcount.ts -->
			<span id="pm-word-count"></span>
			<SaveStatus />
		</div>
	</div>
</div>

<style>
	.editor-footer {
		border-top: 1px solid var(--border);
		position: sticky;
		bottom: 0;
		background-color: var(--box-background);
		transition: background-color 0.2s ease-in-out;
	}
	.editor-footer.suggesting {
		background-color: var(--blue-light);
	}
	.footer-bottom {
		display: flex;
		padding: 10px 30px;
		justify-content: space-between;
		align-items: center;
		width: 100%;
	}
	.footer-left,
	.footer-right {
		display: flex;
		align-items: center;
		gap: 12px;
	}
	.read-only-banner {
		padding: 6px 30px;
		background-color: var(--yellow-light, #fffbe6);
		color: var(--yellow-dark, #8a6d3b);
		font-size: 13px;
		font-weight: 500;
		border-bottom: 1px solid var(--border);
		text-align: center;
	}
	.editor-footer #pm-word-count {
		font-size: 12px;
		color: var(--text-light);
		font-weight: 600;
	}
</style>
