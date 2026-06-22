<script lang="ts">
	import { postLanguageStore } from '../../postStore';
	import Editor from './Editor/Editor.svelte';
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
</style>
