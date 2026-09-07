<script lang="ts">
	import { onMount } from 'svelte';
	import {
		postEditor,
		postTitle,
		postVariantLanguageStore,
		postVariantStore
	} from '../../postStore';
	import Editor from './Editor/Editor.svelte';
	import Title from './Title.svelte';
	import EditorFooter from './Footer/EditorFooter.svelte';

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
			$postTitle?.focus();
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
		<Title bind:this={$postTitle} />
	</label>

	<Editor />
	<EditorFooter />
</div>

<style>
	#post-body {
		display: flex;
		flex-direction: column;
		flex: 1;
		background-color: var(--background);
	}
	#post-body :global(.ProseMirror) {
		width: 760px !important;
		color: var(--text-faded);
	}
	#post-body::selection {
		background: var(--accent-light);
	}
	.top {
		border-radius: 20px 20px 0 0;
		cursor: text;
		padding-top: 15px;
	}
</style>
