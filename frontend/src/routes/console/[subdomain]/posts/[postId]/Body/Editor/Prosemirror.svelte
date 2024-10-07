<script lang="ts">
	import { EditorState } from 'prosemirror-state';
	import schema from '../../../../../lib/prosemirror/schema';
	import { EditorView, type DOMEventMap } from 'prosemirror-view';
	import { createEventDispatcher, onMount } from 'svelte';
	import { getNodeViews } from './nodeviews/nodeviews';
	import { importCodemirrorAll } from '../../../../../lib/components/CodemirrorEditor/codemirror';
	import { getPlugins } from './plugins/plugins';
	import type { ProsemirrorEventDispatchType } from './editorEvents';
	import { postEditingStatusStore } from '../../../postStore';
	import { Loader } from '@hyvor/design/components';

	export let value: string | null;

	let wrap: HTMLDivElement;

	const dispatch = createEventDispatcher<{
		change: string;
		view: EditorView;
		event: ProsemirrorEventDispatchType;
	}>();

	let isLoading = true;

	async function createEditor() {
		isLoading = true;
		await importCodemirrorAll();
		isLoading = false;

		const jsonParsedValue = value ? JSON.parse(value) : null;
		wrap.innerHTML = '';

		let state = EditorState.create({
			schema: schema,
			plugins: getPlugins(),
			doc: value ? schema.nodeFromJSON(jsonParsedValue) : undefined
		});

		function getDomEvents() {
			const events: (keyof HTMLElementEventMap)[] = ['blur', 'focus'];

			return events.reduce(
				(obj, e) => {
					return {
						...obj,
						[e]: <T extends keyof DOMEventMap>(view: EditorView, event: DOMEventMap[T]) =>
							dispatch('event', {
								view,
								name: e,
								event
							})
					};
				},
				{} as Record<keyof DOMEventMap, any>
			);
		}

		const view = new EditorView(wrap, {
			state: state,
			nodeViews: getNodeViews(),
			handleDOMEvents: getDomEvents(),
			// handleClickOn,
			// handleKeyDown,
			dispatchTransaction: (tr) => {
				dispatch('change', JSON.stringify(tr.doc.toJSON()));

				// dispatch a transaction event
				// this event is used by Toc to update itself
				const customEvent = new CustomEvent('prosemirror:transaction', {
					detail: {
						doc: tr.doc
					}
				});
				document.dispatchEvent(customEvent);

				const state = view.state.apply(tr);
				view.updateState(state);
			}
		});

		dispatch('view', view);

		return view;
	}

	onMount(() => {
		createEditor();
	});

	function handleWrapClick(e: MouseEvent) {
		if (e.target === wrap) $postEditingStatusStore.editorView?.focus();
	}
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div
	class="pm-editor"
	bind:this={wrap}
	on:click={handleWrapClick}
	on:keyup
	class:loaded={!isLoading}
>
	{#if isLoading}
		<Loader block padding={250} />
	{/if}
</div>

<style lang="scss">
	.pm-editor {
		--prosemirror-hover-outline: 2px solid #8cf;
		--prosemirror-selected-outline: 3px solid #299af3;

		&.loaded {
			padding-bottom: 100px;
		}

		:global(.ProseMirror) {
			position: relative;
			font-size: 18px;
			padding: 25px 30px;
			position: relative;
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
			&:focus-visible {
				outline: none;
			}
		}

		:global(.ProseMirror-hideselection *::selection) {
			background: transparent;
		}
		:global(.ProseMirror-hideselection *::-moz-selection) {
			background: transparent;
		}

		:global(.ProseMirror-hideselection) {
			caret-color: transparent;
		}

		:global(.ProseMirror-selectednode) {
			outline: var(--prosemirror-selected-outline) !important;
		}
		:global(img.ProseMirror-separator) {
			display: inline !important;
			border: none !important;
			margin: 0 !important;
		}

		// placeholder plugin
		:global(.ProseMirror[data-placeholder]::before) {
			color: var(--text-light);
			position: absolute;
			content: attr(data-placeholder);
			pointer-events: none;
			line-height: 30px;
		}

		:global(.ProseMirror > *:first-child) {
			margin-top: 0 !important;
		}

		:global(blockquote),
		:global(figure),
		:global(h1),
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5),
		:global(h6),
		:global(p),
		:global(pre),
		:global(ul),
		:global(ol) {
			margin: 30px 0 0 0;
		}

		// === NODES

		// paragraph
		:global(p) {
			line-height: 30px;
			margin-top: 30px;
			letter-spacing: 0.2px;
		}

		// heading

		:global(.heading-wrap) {
			position: relative;
			:global(.heading-details) {
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
			:global(input) {
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
			:global(.input-wrap) {
				display: flex;
				flex: 1;
				margin-left: 4px;
			}
			:global(+ *) {
			}
		}

		:global(.heading-selectors-wrap) {
			display: flex;

			:global(button) {
				font-size: 10px;
				background-color: var(--input);
				padding: 2px 4px;
				margin-right: 2px;
				border-radius: 2px;
				opacity: 0.3;
				transition: 0.2s opacity;
			}
			&:hover :global(button) {
				opacity: 0.5;
			}
			:global(button:hover) {
				opacity: 0.7;
			}
			:global(button.selected) {
				background-color: var(--gray-light);
				opacity: 1;
			}
		}

		:global(h1),
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5),
		:global(h6) {
			margin-top: 35px;
		}

		:global(h1) {
			font-size: 2em;
		}
		:global(h2) {
			font-size: 1.5em;
		}
		:global(h3) {
			font-size: 1.3em;
		}
		:global(h4) {
			font-size: 1.2em;
		}
		:global(h5) {
			font-size: 1.1em;
		}
		:global(h6) {
			font-size: 1em;
		}

		// hr
		:global(hr) {
			margin: 30px 0;
			border-top: 2px solid var(--grey);
		}

		// blockquote and callout
		:global(blockquote),
		:global(aside) {
			margin-top: 30px;
			border-width: 0;
			border-color: #000000;
			border-style: solid;
			border-left-width: 4px;
			padding: 10px 15px;
			:global(*:first-child) {
				margin-top: 0;
			}
		}

		// callout
		:global(aside) {
			border-left: none;
			border-radius: 5px;
			display: flex;
			padding: 0;
			position: relative;
			:global(.emoji-icon) {
				text-align: center;
				cursor: pointer;
				user-select: none;
				display: inline-block;
				padding: 10px 12px;
			}
			:global(.content-div) {
				flex: 1;
				padding: 10px 10px 10px 0;
			}
			:global(.color-pickers-wrap) {
				position: absolute;
				right: 0;
				bottom: 100%;
			}
		}

		:global(.button-wrap) {
			position: relative;
			
			display: inline-block;
			text-decoration: none;
			background-color: #bbbaba;
			border-radius: 10px;
			padding: 10px 10px 10px 10px;

			:global(.content-div) {
	
			}
		}

		// figure (embed and image)
		:global(figure) {
			margin-top: 45px;

			:global(&:hover) {
				outline: var(--prosemirror-hover-outline);
			}

			:global(figcaption) {
				padding: 7px;
				font-size: 14px;
				text-align: center;
				margin-top: 22px;
			}
			:global(figcaption.empty:before) {
				content: 'Enter caption...';
				color: #aaa;
				position: absolute;
				left: 50%;
				transform: translateX(-50%);
				pointer-events: none;
			}

			:global(x-embed) {
				position: relative;
				display: block;
				&:before {
					content: '';
					position: absolute;
					z-index: 1;
					width: 100%;
					height: 100%;
					top: 0;
					left: 0;
				}
			}
		}

		// img
		:global(img) {
			display: block;
			margin: auto;
			object-fit: cover;
			max-width: 100%;
		}

		// lists
		:global(li) {
			:global(> *) {
				margin: 5px 0 !important;
			}
		}

		// code block
		:global(.code-wrap) {
			margin-top: 30px;
			:global(.code-toolbar) {
				white-space: normal;
				padding: 10px;
				background: var(--input);
				border-radius: 20px 20px 0 0;
				border-bottom: 1px solid #dddddd;
			}
			:global(.code-toolbar-labels) {
				display: flex;
				font-size: 12px;
				:global(div) {
					flex: 1;
					padding-left: 4px;
				}
			}
			:global(.code-toolbar-inputs) {
				display: flex;
				:global(input) {
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
			}

			:global(.CodeMirror) {
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
			:global(.topbar) {
				background: var(--input);
				border-radius: 20px 20px 0 0;
				border-bottom: 1px solid #dddddd;
				font-size: 12px;
				padding: 10px 15px;
			}
			:global(.code-toolbar-quit-message) {
				position: absolute;
				bottom: 0;
				right: 0;
				font-size: 10px;
				padding-right: 10px;
				color: var(--text-light);
			}
		}

		// inline

		:global(:not(pre) > code) {
			background: rgba(135, 131, 120, 0.15);
			color: #eb5757;
			border-radius: 3px;
			font-size: 85%;
			padding: 0.2em 0.4em;
			font-family: monospace;
		}

		:global(a) {
			color: var(--link);
			text-decoration: underline;
		}

		:global(mark) {
			padding: 0.2em 0.4em;
			background-color: #fcf8e3;
		}

		:global(.table-wrap) {
			margin-top: 30px;
			:global(.table-middle) {
				overflow-x: auto;
			}
		}

		:global(table) {
			margin: 0;
			margin-top: 5px;
			border: 1px solid black;
			border-collapse: collapse;
			table-layout: fixed;
			white-space: break-spaces;

			:global(tr) {
				height: 20px;
				width: 150px;
			}

			:global(th),
			:global(td) {
				width: 150px;
				height: 40px;
				border: 1px solid #ddd;
				padding: 7px 15px;
				vertical-align: top;
				box-sizing: border-box;
				position: relative;
				:global(p) {
					margin-top: 0;
				}
			}

			:global(th) {
				font-weight: bold;
				text-align: left;
			}

			:global(.column-resize-handle) {
				position: absolute;
				right: -2px;
				top: 0;
				bottom: -2px;
				width: 4px;
				background-color: #adf;
				cursor: col-resize;
			}

			:global(.selectedCell:after) {
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
		}
	}
</style>
