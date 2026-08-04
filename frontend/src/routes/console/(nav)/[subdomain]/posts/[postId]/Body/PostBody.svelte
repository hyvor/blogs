<script lang="ts">
	import { postVariantLanguageStore } from '../../postStore';
	import SaveStatus from './Footer/SaveStatus.svelte';
	import Editor from './Editor/Editor.svelte';
	import AutoTranslate from './Editor/EditorTop/AutoTranslate/AutoTranslate.svelte';
	import Title from './Top/Title.svelte';
</script>

<div
	id="post-body"
	class="hds-box"
	spellcheck={false}
	dir={$postVariantLanguageStore!.direction}
	style="
        dir: {$postVariantLanguageStore!.direction};
        text-align: {$postVariantLanguageStore!.direction === 'rtl' ? 'right' : 'left'};
        font-family: {$postVariantLanguageStore!.direction === 'rtl' ? 'sans-serif' : 'inherit'};
    "
>
	<div class="top">
		<Title />
	</div>

	<Editor />

	<div class="editor-footer">
		<div class="footer-left">
			<!-- see plugin-wordcount.ts -->
			<span id="pm-word-count"></span>
			<AutoTranslate />
		</div>

		<div class="footer-right">
			<SaveStatus />
		</div>
	</div>
</div>

<style>
	#post-body {
		display: flex;
		flex-direction: column;
		min-height: calc(100vh - 70px);
	}
	.top {
		display: flex;
		padding: 10px 30px;
		background: var(--hover);
		border-radius: 20px 20px 0 0;
	}
	.editor-footer {
		padding: 10px 30px;
		border-top: 1px solid var(--border);
		position: sticky;
		bottom: 0;
		background: var(--box-background);
		border-radius: 0 0 20px 20px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.editor-footer #pm-word-count {
		font-size: 12px;
		color: var(--text-light);
		font-weight: 600;
	}
</style>
