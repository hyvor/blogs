<script lang="ts">
	import { onMount } from 'svelte';
	import './codemirror';
	import { CODEMIRROR_MODES, importCodemirrorAll } from './codemirror';

	interface Props {
		value: string;
		ext: keyof typeof CODEMIRROR_MODES;
		id?: string | number;
		onchange?: (value: string) => void;
		onsave?: (value: string) => void;
		[key: string]: any;
	}

	let { value = $bindable(), ext, id = '', onchange, onsave, ...rest }: Props = $props();

	let lastId = id;

	let tabSize = $derived(ext === 'yaml' ? 2 : 4);

	let editorDiv: HTMLDivElement | undefined = $state();
	let cm: any;

	async function initCm() {
		await importCodemirrorAll();

		editorDiv!.innerHTML = '';

		function handleSave(cm: any) {
			onsave?.(cm.doc.getValue());
		}

		function handleTab(cm: any) {
			if (cm.somethingSelected()) {
				cm.indentSelection('add');
			} else {
				cm.replaceSelection(
					cm.getOption('indentWithTabs') ? '\t' : Array(cm.getOption('indentUnit') + 1).join(' '),
					'end',
					'+input'
				);
			}
		}

		cm = (window as any).CodeMirror(editorDiv, {
			value,
			mode: CODEMIRROR_MODES[ext],
			theme: 'solarized',
			keyMap: 'sublime',
			tabSize,
			indentWithTabs: false,
			indentUnit: tabSize,
			lineWrapping: true,
			lineNumbers: true,
			matchBrackets: true,
			matchTags: { bothTags: true },
			autoCloseBrackets: true,
			autoCloseTags: true,
			extraKeys: {
				'Ctrl-S': handleSave,
				'Cmd-S': handleSave,
				Tab: handleTab
			}
		});
		cm.on('change', function () {
			const val = cm.doc.getValue();
			value = val;
			onchange?.(val);
		});
	}

	let readyPromise: Promise<void> | undefined;

	onMount(() => {
		readyPromise = initCm();
	});

	// re-create codemirror instance when id changes
	$effect(() => {
		if (lastId !== id) {
			readyPromise = initCm();
			lastId = id;
		}
	});

	function focusAtEnd() {
		cm?.focus();
	}

	export async function focus() {
		await readyPromise;
		focusAtEnd();
	}

	function handleEditorClick(e: MouseEvent) {
		if (e.target === editorDiv) {
			focusAtEnd();
		}
	}
</script>

<div class="editor" bind:this={editorDiv} {...rest} onclick={handleEditorClick}></div>

<style lang="scss">
	.editor {
		height: 100%;
		cursor: text;
	}

	.editor :global(.CodeMirror) {
		height: 100%;
		font-family: 'source-code-pro', Menlo, 'Courier New', Consolas, monospace !important;
		box-shadow: none !important;
		border-radius: 20px;
		background-color: var(--input) !important;
		font-size: 14px;
		line-height: 21px;
		:global(.CodeMirror-line) {
			padding-left: 15px !important;
		}
		:global(.CodeMirror-gutters) {
			background-color: var(--input);
		}
		:global(.CodeMirror-scroll) {
			overflow-x: hidden !important;
		}
	}
</style>
