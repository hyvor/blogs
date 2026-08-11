<script lang="ts">
	import { postLanguageStore } from '../../postStore';
	import Editor from './Editor/Editor.svelte';
	import AutoTranslate from './Editor/EditorTop/AutoTranslate/AutoTranslate.svelte';
	import Title from './Top/Title.svelte';
</script>

<div
	id="post-body"
	class="hds-box"
	spellcheck={false}
	dir={$postLanguageStore.direction}
	style="
        dir: {$postLanguageStore.direction};
        text-align: {$postLanguageStore.direction === 'rtl' ? 'right' : 'left'};
        font-family: {$postLanguageStore.direction === 'rtl' ? 'sans-serif' : 'inherit'};
    "
>
	<div class="top">
		<Title />
	</div>

	<Editor />

	<div class="editor-footer">
		<!-- see plugin-wordcount.ts -->
		<span id="pm-word-count"></span>
		<AutoTranslate />
	</div>
</div>

<style>
	#post-body {
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	.top {
		display: flex;
		padding: 10px 25px;
		background: var(--hover);
		border-radius: 20px 20px 0 0;
	}
	.editor-footer {
		padding: 10px 25px;
		border-top: 1px solid var(--border);
		position: sticky;
		bottom: 0;
		background: var(--box-background);
	}
	.editor-footer #pm-word-count {
		font-size: 12px;
		color: var(--text-light);
		font-weight: 600;
	}
</style>
