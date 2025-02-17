<script lang="ts">
	import type { EditorView } from 'prosemirror-view';
	import type { SlashOption } from './options';
	import { onMount, tick } from 'svelte';
	import { Node } from 'prosemirror-model';
	import schema from '../../schema';
	import { NodeSelection, TextSelection } from 'prosemirror-state';
	import { editorStore } from '../../store';

	interface Props {
		view: EditorView;
		show?: boolean;
		options?: SlashOption[];
	}

	let { view, show = $bindable(false), options = [] }: Props = $props();

	let active: SlashOption | undefined = $state(options[0]);

	$effect(() => {
		active = options[0];
	});

	let slashEl: HTMLDivElement | undefined = $state(undefined);

	async function updatePosition() {
		await tick();

		if (!slashEl) return;

		const posTop = view.coordsAtPos(view.state.selection.from).top;

		// The box in which the slash view is positioned, to use as base
		const wrapPos = slashEl.offsetParent!.getBoundingClientRect();
		const viewPos = view.dom.getBoundingClientRect();
		const spaceBelow = wrapPos.bottom - posTop;
		const spaceAbove = posTop - wrapPos.top;

		if (spaceAbove > spaceBelow) {
			slashEl.style.bottom = spaceBelow + 'px';
			slashEl.style.top = 'auto';
			slashEl.classList.add('top');
			slashEl.classList.remove('bottom');
		} else {
			slashEl.style.top = posTop - wrapPos.top + 35 + 'px';
			slashEl.classList.add('bottom');
			slashEl.classList.remove('top');
		}

		const isRtl = $editorStore.props.rtl;

		if (isRtl) {
			slashEl.style.left = 'auto';
			slashEl.style.right = wrapPos.right - viewPos.right + 25 + 'px';
		} else {
			slashEl.style.left = viewPos.left - wrapPos.left + 'px';
		}
	}

	$effect(() => {
		show;
		updatePosition();
	});

	function houseMouseOver(option: SlashOption) {
		active = option;
	}

	function handleKeydown(e: KeyboardEvent) {
		if (isCreatingNode) return;

		if (!show) return;

		if (e.key === 'Escape') {
			show = false;
			e.stopPropagation();
		} else if (e.key === 'ArrowDown') {
			e.preventDefault();
			active = options[active ? (options.indexOf(active) + 1) % options.length : 0];
			scrollToActive();
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			active =
				options[
					active
						? (options.indexOf(active) - 1 + options.length) % options.length
						: options.length - 1
				];
			scrollToActive();
		} else if (e.key === 'Enter') {
			e.preventDefault();
			if (active) {
				handleClick(active);
			}
		}
	}

	async function scrollToActive() {
		await tick();
		const active = slashEl?.querySelector('.option.active');
		if (active) {
			active.scrollIntoView(false);
		}
	}

	let isCreatingNode = false;

	async function handleClick(option: SlashOption) {
		let node: Node;

		if (isCreatingNode) return;

		if (typeof option.node === 'string') {
			node = schema.nodes[option.node]!.create(option.attrs);
		} else {
			isCreatingNode = true;
			const tempNode = await option.node();
			isCreatingNode = false;
			if (!tempNode) {
				view.focus();
				return;
			}
			node = tempNode;
		}

		let sel = view.state.selection,
			pos;
		let same = sel.$from.sharedDepth(sel.to);
		pos = sel.$from.before(same);
		const nodeSel = NodeSelection.create(view.state.doc, pos);

		const tr = view.state.tr;

		view.dispatch(tr.replaceWith(nodeSel.from, nodeSel.to, node));

		const tr2 = view.state.tr;

		view.dispatch(
			tr2.setSelection(TextSelection.create(tr.doc, pos + 1)).scrollIntoView()
			/*  tr2
                .setSelection(
                    m.focusInput ? NodeSelection.create(tr2.doc, pos + 1) :
                    m.focusCell
                    ? TextSelection.create(tr2.doc, pos + 2)
                    :   m.selectNode
                        ? NodeSelection.create(tr.doc, pos)
                        : TextSelection.create(tr.doc, pos + 1)
                )
                .scrollIntoView() */
		);

		view.focus();

		show = false;
	}

	onMount(() => {
		window.addEventListener('keydown', handleKeydown, true);
		return () => {
			window.removeEventListener('keydown', handleKeydown, true);
		};
	});
</script>

{#if show}
	<div class="wrap" bind:this={slashEl}>
		{#each options as option (option.name)}
			<div
				class="option"
				onclick={() => handleClick(option)}
				onmouseover={() => houseMouseOver(option)}
				onfocus={() => houseMouseOver(option)}
				onkeyup={(e) => {
					if (e.key === 'Enter') {
						e.stopPropagation();
						handleClick(option);
					}
				}}
				role="button"
				tabindex="0"
				class:active={option.name === active?.name}
			>
				<div class="icon">
					<option.icon />
				</div>
				<div class="name-desc">
					<div class="name">{option.name}</div>
					<div class="desc">{option.description}</div>
				</div>
			</div>
		{/each}
	</div>
{/if}

<style lang="scss">
	.wrap {
		position: absolute;
		width: 325px;
		max-height: 275px;
		overflow: auto;
		left: 50px;
		margin-left: 30px;
		background: var(--box-background);
		box-shadow: 0 0 20px 2px rgba(0, 0, 0, 0.2);
		border-radius: var(--box-radius);
		z-index: 1000000;
	}

	.option {
		padding: 8px 14px;
		cursor: pointer;
		position: relative;
		display: flex;

		&.active {
			background-color: var(--accent-light-mid);
		}

		.icon {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 30px;
			font-size: 20px;
			flex-shrink: 0;
		}

		.name-desc {
			margin-left: 8px;
		}

		.name {
			font-weight: 600;
		}

		.desc {
			font-size: 12px;
			color: var(--text-light);
		}
	}
</style>
