<script lang="ts">
	import { onMount } from 'svelte';
	import { postEditor, postVariantLanguageStore, postVariantStore } from '../../postStore';
	import SaveStatus from './Footer/SaveStatus.svelte';
	import SuggestionModeToggle from './Footer/SuggestionModeToggle.svelte';
	import Editor from './Editor/Editor.svelte';
	import AutoTranslate from './Editor/EditorTop/AutoTranslate/AutoTranslate.svelte';
	import Title from './Top/Title.svelte';
	import PublishedNotice from './Footer/PublishedNotice.svelte';

	let titleComponent: { focus: () => void } | undefined = $state();
	let editorKey = $derived(String($postVariantStore.id));

	// the editor loads its ProseMirror view asynchronously (onMount awaits a
	// dynamic import), so it may not be ready yet right after this component mounts
	function focusEditorWhenReady(attemptsLeft = 50) {
		if ($postEditor?.getView()) {
			$postEditor.focus();
		} else if (attemptsLeft > 0) {
			requestAnimationFrame(() => focusEditorWhenReady(attemptsLeft - 1));
		}
	}

	onMount(() => {
		const title = ($postVariantStore.title || '').trim();
		if (title === '') {
			titleComponent?.focus();
		} else {
			focusEditorWhenReady();
		}
	});
</script>

<div
	id="post-body"
	spellcheck={false}
	dir={$postVariantLanguageStore!.direction}
	style="
        dir: {$postVariantLanguageStore!.direction};
        text-align: {$postVariantLanguageStore!.direction === 'rtl' ? 'right' : 'left'};
        font-family: {$postVariantLanguageStore!.direction === 'rtl' ? 'sans-serif' : 'inherit'};
    "
>
	<label class="top">
		<Title bind:this={titleComponent} />
	</label>

	{#key editorKey}
		<Editor />
	{/key}

	<div class="editor-footer">
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
</div>

<style>
	#post-body {
		display: flex;
		flex-direction: column;
		min-height: calc(100vh - 70px);
	}
	#post-body :global(.ProseMirror) {
		width: 760px !important;
		color: var(--text-faded);
	}
	.top {
		border-radius: 20px 20px 0 0;
		cursor: text;
	}
	.editor-footer {
		border-top: 1px solid var(--border);
		position: sticky;
		bottom: 0;
		background: var(--box-background);
		border-radius: 0 0 20px 20px;
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
	.editor-footer #pm-word-count {
		font-size: 12px;
		color: var(--text-light);
		font-weight: 600;
	}
</style>
