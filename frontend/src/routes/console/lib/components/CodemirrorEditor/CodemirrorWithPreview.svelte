<script lang="ts">
	import { Modal, Textarea } from '@hyvor/design/components';
	import CodemirrorEditor from './CodemirrorEditor.svelte';
	import { createEventDispatcher } from 'svelte';

	interface Props {
		value: string;
		title: string;
	}

	let { value, title }: Props = $props();

	let modalOpen = $state(false);

	const dispatch = createEventDispatcher<{
		change: string;
		confirm: void;
	}>();

	function handleConfirm() {
		modalOpen = false;
		dispatch('confirm');
	}

	function handleEditorChange(e: any) {
		dispatch('change', e.detail);
	}
</script>

<div class="wrap">
	<Textarea block rows={4} {value} style="resize:none" />
	<div
		class="overlay"
		onclick={() => (modalOpen = true)}
		onkeyup={(e) => e.key === 'Enter' && (modalOpen = true)}
		role="button"
		tabindex="0"
	></div>
</div>

{#if modalOpen}
	<Modal
		{title}
		size="large"
		bind:show={modalOpen}
		footer={{
			confirm: {
				text: 'Save'
			}
		}}
		on:confirm={handleConfirm}
	>
		<div class="codemirror-wrap">
			<CodemirrorEditor id="code" ext="twig" {value} on:change={handleEditorChange} />
		</div>
	</Modal>
{/if}

<style>
	.wrap {
		position: relative;
	}
	.overlay {
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		cursor: pointer;
	}
	.codemirror-wrap {
		height: 100%;
		overflow: auto;
	}
	.codemirror-wrap :global(.CodeMirror) {
		min-height: 400px;
	}
</style>
