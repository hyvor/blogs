<script lang="ts">
	import { Modal, Textarea } from '@hyvor/design/components';
	import CodemirrorEditor from './CodemirrorEditor.svelte';
	import { getI18n } from '../../i18n';

	const i18n = getI18n();

	interface Props {
		value: string;
		title: string;
		onchange?: (value: string) => void;
		onconfirm?: () => void;
	}

	let { value, title, onchange, onconfirm }: Props = $props();

	let modalOpen = $state(false);
	let editorRef: ReturnType<typeof CodemirrorEditor> | undefined = $state();

	function handleConfirm() {
		modalOpen = false;
		onconfirm?.();
	}

	$effect(() => {
		if (modalOpen) {
			editorRef?.focus();
		}
	});

	function handleWrapClick(e: MouseEvent) {
		if (e.target === e.currentTarget) {
			editorRef?.focus();
		}
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
				text: i18n.t('console.common.save')
			}
		}}
		on:confirm={handleConfirm}
	>
		<!-- svelte-ignore a11y_click_events_have_key_events, a11y_no_static_element_interactions -->
		<div class="codemirror-wrap" onclick={handleWrapClick}>
			<CodemirrorEditor bind:this={editorRef} id="code" ext="twig" {value} {onchange} />
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
