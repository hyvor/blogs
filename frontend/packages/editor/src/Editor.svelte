<script lang="ts">
	import { EditorState } from 'prosemirror-state';
	import schema from './schema';
	import { EditorView, type DOMEventMap } from 'prosemirror-view';
	import { onMount } from 'svelte';
	import { getNodeViews } from './nodeviews/nodeviews';
	import { getPlugins } from './plugins/plugins';
	import { Loader } from '@hyvor/design/components';
	import { editorContent, editorStore, type Props } from './store';
	import { importCodemirrorAll } from './codemirror';
	import { getMarkViews } from './markviews/markviews';

	let props: Props = $props();

	let wrap: HTMLDivElement | undefined = $state();

	let isLoading = $state(true);
	let view: EditorView | undefined;

	async function createEditor() {
		isLoading = true;
		await importCodemirrorAll();
		isLoading = false;

		const jsonParsedValue = props.value ? JSON.parse(props.value) : null;
		wrap!.innerHTML = '';

		let state = EditorState.create({
			schema: schema,
			plugins: getPlugins(),
			doc: props.value ? schema.nodeFromJSON(jsonParsedValue) : undefined
		});

		function getDomEvents() {
			const events: (keyof HTMLElementEventMap)[] = ['blur', 'focus'];
			return events.reduce(
				(obj, e) => {
					return {
						...obj,
						[e]: <T extends keyof DOMEventMap>(view: EditorView, event: DOMEventMap[T]) =>
							props.ondomevent?.(e, event)
					};
				},
				{} as Record<keyof DOMEventMap, any>
			);
		}

		view = new EditorView(wrap!, {
			state: state,
			nodeViews: getNodeViews(),
			markViews: getMarkViews(),
			handleDOMEvents: getDomEvents(),
			// handleClickOn,
			// handleKeyDown,
			dispatchTransaction: (tr) => {
				const docJson = JSON.stringify(tr.doc.toJSON());
				editorContent.set(docJson);

				props.onvaluechange?.(docJson);

				const state = view!.state.apply(tr);
				view!.updateState(state);
			}
		});

		editorStore.set({ view, props });

		return view;
	}

	onMount(() => {
		createEditor();
	});

	function handleWrapClick(e: MouseEvent | KeyboardEvent) {
		if (e.target === wrap) view?.focus();
	}
</script>

<!-- svelte-ignore a11y_no_static_element_interactions -->
<div
	class="pm-editor"
	bind:this={wrap}
	onclick={handleWrapClick}
	onkeyup={(e) => e.key === 'Enter' && handleWrapClick(e)}
	class:loaded={!isLoading}
>
	{#if isLoading}
		<Loader block padding={250} />
	{/if}
</div>

<style>
	.pm-editor {
		--prosemirror-hover-outline: 2px solid #8cf;
		--prosemirror-selected-outline: 3px solid #299af3;
	}

	.pm-editor.loaded {
		padding-bottom: 100px;
	}

	.pm-editor :global(.ProseMirror) {
		position: relative;
		font-size: 18px;
		padding: 25px 30px;
		min-height: 620px;
		margin: auto;
		width: 700px;
		max-width: 100%;
		word-wrap: break-word;
		white-space: pre-wrap;
		white-space: break-spaces;
		-webkit-font-variant-ligatures: none;
		font-variant-ligatures: none;
		font-feature-settings: 'liga' 0;
	}

	.pm-editor :global(.ProseMirror:focus-visible) {
		outline: none;
	}

	.pm-editor :global(.ProseMirror-hideselection *::selection) {
		background: transparent;
	}

	.pm-editor :global(.ProseMirror-hideselection *::-moz-selection) {
		background: transparent;
	}

	.pm-editor :global(.ProseMirror-hideselection) {
		caret-color: transparent;
	}

	.pm-editor :global(.ProseMirror-selectednode) {
		outline: var(--prosemirror-selected-outline) !important;
	}

	.pm-editor :global(img.ProseMirror-separator) {
		display: inline !important;
		border: none !important;
		margin: 0 !important;
	}

	.pm-editor :global(.ProseMirror[data-placeholder]::before) {
		color: var(--text-light);
		position: absolute;
		content: attr(data-placeholder);
		pointer-events: none;
		line-height: 30px;
	}

	.pm-editor :global(.ProseMirror > *:first-child) {
		margin-top: 0 !important;
	}

	.pm-editor :global(blockquote),
	.pm-editor :global(figure),
	.pm-editor :global(h1),
	.pm-editor :global(h2),
	.pm-editor :global(h3),
	.pm-editor :global(h4),
	.pm-editor :global(h5),
	.pm-editor :global(h6),
	.pm-editor :global(p),
	.pm-editor :global(pre),
	.pm-editor :global(ul),
	.pm-editor :global(ol) {
		margin: 30px 0 0 0;
	}

	.pm-editor :global(p) {
		line-height: 30px;
		margin-top: 30px;
		letter-spacing: 0.2px;
	}

	.pm-editor :global(.heading-wrap) {
		position: relative;
	}

	.pm-editor :global(.heading-wrap .heading-details) {
		position: absolute;
		bottom: 100%;
		left: 0;
		color: var(--text-light);
		font-size: 12px;
		margin-bottom: 4px;
		display: flex;
		flex-direction: row;
		width: 100%;
		align-items: center;
	}

	.pm-editor :global(.heading-wrap input) {
		padding: 0;
		background: transparent;
		border: none;
		width: 100%;
		outline: none;
		flex: 1;
		display: block;
		font-family: inherit;
		font-size: inherit;
		margin-left: 1px;
	}

	.pm-editor :global(.heading-wrap .input-wrap) {
		display: flex;
		flex: 1;
		margin-left: 4px;
	}

	.pm-editor :global(.heading-selectors-wrap) {
		display: flex;
	}

	.pm-editor :global(.heading-selectors-wrap button) {
		font-size: 10px;
		background-color: var(--input);
		padding: 2px 4px;
		margin-right: 2px;
		border-radius: 2px;
		opacity: 0.3;
		transition: 0.2s opacity;
	}

	.pm-editor :global(.heading-selectors-wrap:hover button) {
		opacity: 0.5;
	}

	.pm-editor :global(.heading-selectors-wrap button:hover) {
		opacity: 0.7;
	}

	.pm-editor :global(.heading-selectors-wrap button.selected) {
		background-color: var(--gray-light);
		opacity: 1;
	}

	.pm-editor :global(h1),
	.pm-editor :global(h2),
	.pm-editor :global(h3),
	.pm-editor :global(h4),
	.pm-editor :global(h5),
	.pm-editor :global(h6) {
		margin-top: 35px;
	}

	.pm-editor :global(h1) {
		font-size: 2em;
	}

	.pm-editor :global(h2) {
		font-size: 1.5em;
	}

	.pm-editor :global(h3) {
		font-size: 1.3em;
	}

	.pm-editor :global(h4) {
		font-size: 1.2em;
	}

	.pm-editor :global(h5) {
		font-size: 1.1em;
	}

	.pm-editor :global(h6) {
		font-size: 1em;
	}

	.pm-editor :global(hr) {
		margin: 30px 0;
		border-top: 2px solid var(--grey);
	}

	.pm-editor :global(blockquote),
	.pm-editor :global(aside) {
		margin-top: 30px;
		border-width: 0;
		border-color: #000000;
		border-style: solid;
		border-left-width: 4px;
		padding: 10px 15px;
	}

	.pm-editor :global(blockquote *:first-child),
	.pm-editor :global(aside *:first-child) {
		margin-top: 0;
	}

	.pm-editor :global(aside) {
		border-left: none;
		border-radius: 5px;
		display: flex;
		padding: 0;
		position: relative;
	}

	.pm-editor :global(aside .emoji-icon) {
		text-align: center;
		cursor: pointer;
		user-select: none;
		display: inline-block;
		padding: 10px 12px;
	}

	.pm-editor :global(aside .content-div) {
		flex: 1;
		padding: 10px 10px 10px 0;
	}

	.pm-editor :global(aside .color-pickers-wrap) {
		position: absolute;
		right: 0;
		bottom: 100%;
	}

	.pm-editor :global(figure) {
		margin-top: 45px;
	}

	.pm-editor :global(figure:hover) {
		outline: var(--prosemirror-hover-outline);
	}

	.pm-editor :global(figure figcaption) {
		padding: 7px;
		font-size: 14px;
		text-align: center;
		margin-top: 22px;
	}

	.pm-editor :global(figure figcaption.empty:before) {
		content: 'Enter caption...';
		color: #aaa;
		position: absolute;
		left: 50%;
		transform: translateX(-50%);
		pointer-events: none;
	}

	.pm-editor :global(figure x-embed) {
		position: relative;
		display: block;
	}

	.pm-editor :global(figure x-embed:before) {
		content: '';
		position: absolute;
		z-index: 1;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
	}

	.pm-editor :global(img) {
		display: block;
		margin: auto;
		object-fit: cover;
		max-width: 100%;
	}

	.pm-editor :global(li > *) {
		margin: 5px 0 !important;
	}

	.pm-editor :global(.code-wrap) {
		margin-top: 30px;
	}

	.pm-editor :global(.code-wrap .code-toolbar) {
		white-space: normal;
		padding: 10px;
		background: var(--input);
		border-radius: 20px 20px 0 0;
		border-bottom: 1px solid #dddddd;
	}

	.pm-editor :global(.code-wrap .code-toolbar-labels) {
		display: flex;
		font-size: 12px;
	}

	.pm-editor :global(.code-wrap .code-toolbar-labels div) {
		flex: 1;
		padding-left: 4px;
	}

	.pm-editor :global(.code-wrap .code-toolbar-inputs) {
		display: flex;
	}

	.pm-editor :global(.code-wrap .code-toolbar-inputs input) {
		flex: 1;
		min-width: 0;
		margin-right: 5px;
		padding: 5px 10px;
		font-size: 12px;
		margin-top: 5px;
		background: #fff;
		border: none;
		border-radius: 20px;
		font-family: inherit;
	}

	.pm-editor :global(.code-wrap .CodeMirror) {
		font-size: 14px;
		height: initial;
		padding: 5px 0;
		padding-bottom: 15px;
		border-radius: 0 0 20px 20px;
		font-family:
			source-code-pro,
			Menlo,
			Courier New,
			Consolas,
			monospace !important;
		box-shadow: none !important;
		background-color: var(--input);
	}

	.pm-editor :global(.code-wrap .topbar) {
		background: var(--input);
		border-radius: 20px 20px 0 0;
		border-bottom: 1px solid #dddddd;
		font-size: 12px;
		padding: 10px 15px;
	}

	.pm-editor :global(.code-wrap .code-toolbar-quit-message) {
		position: absolute;
		bottom: 0;
		right: 0;
		font-size: 10px;
		padding-right: 10px;
		color: var(--text-light);
	}

	.pm-editor :global(:not(pre) > code) {
		background: rgba(135, 131, 120, 0.15);
		color: #eb5757;
		border-radius: 3px;
		font-size: 85%;
		padding: 0.2em 0.4em;
		font-family: monospace;
	}

	.pm-editor :global(a) {
		color: var(--link);
		text-decoration: underline;
	}

	.pm-editor :global(mark) {
		padding: 0.2em 0.4em;
		background-color: #fcf8e3;
	}

	.pm-editor :global(.table-wrap) {
		margin-top: 30px;
	}

	.pm-editor :global(.table-wrap .table-middle) {
		overflow-x: auto;
	}

	.pm-editor :global(table) {
		margin: 0;
		margin-top: 5px;
		border: 1px solid black;
		border-collapse: collapse;
		table-layout: fixed;
		white-space: break-spaces;
	}

	.pm-editor :global(table tr) {
		height: 20px;
		width: 150px;
	}

	.pm-editor :global(table th),
	.pm-editor :global(table td) {
		width: 150px;
		height: 40px;
		border: 1px solid #ddd;
		padding: 7px 15px;
		vertical-align: top;
		box-sizing: border-box;
		position: relative;
	}

	.pm-editor :global(table th p),
	.pm-editor :global(table td p) {
		margin-top: 0;
	}

	.pm-editor :global(table th) {
		font-weight: bold;
		text-align: left;
	}

	.pm-editor :global(table .column-resize-handle) {
		position: absolute;
		right: -2px;
		top: 0;
		bottom: -2px;
		width: 4px;
		background-color: #adf;
		cursor: col-resize;
	}

	.pm-editor :global(table .selectedCell:after) {
		z-index: 2;
		position: absolute;
		content: '';
		left: 0;
		right: 0;
		top: 0;
		bottom: 0;
		background: rgba(200, 200, 255, 0.4);
		pointer-events: none;
		cursor: default;
	}

	.pm-editor :global(.user-comment) {
		border-bottom: 3px solid #e0d32e;
		cursor: pointer;
	}

	.pm-editor :global(.comment-tick) {
		position: fixed;
		width: 16px;
		height: 16px;
		border: 8px solid transparent;
		border-left-color: #e0d32e;
		transform: translateY(-50%);
	}
</style>
