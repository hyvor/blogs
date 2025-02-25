<script lang="ts">
	import { Editor } from 'editor';
	import { onMount } from 'svelte';

	let loaded = $state(false);
	let value = $state<string | null>(null);

	onMount(() => {
		value = localStorage.getItem('editor-page-content');
		loaded = true;
	});

	function handleChange(newValue: string) {
		localStorage.setItem('editor-page-content', newValue);
	}
</script>

<div class="wrap hds-box">
	{#if loaded}
		<Editor {value} onvaluechange={handleChange} />
	{/if}
</div>

<style>
	.wrap {
		width: 650px;
		margin: 20px auto;
		height: calc(100vh - 40px);
	}
</style>
