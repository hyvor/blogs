<script lang="ts">
	import { Modal, Button } from '@hyvor/design/components';
	import { blogListStore, blogSelectorOpenStore } from '../../stores';
	import { blogStore } from '../../stores/blogStore';
	import IconGripVertical from '@hyvor/icons/IconGripVertical';
	import IconPlus from '@hyvor/icons/IconPlus';

	import { flip } from 'svelte/animate';
	import { dndzone, SOURCES, TRIGGERS } from 'svelte-dnd-action';
	import type { BlogList } from '../../types';
	import { saveSort } from '../../actions/blogActions';
	import arrowSvg from './drag-note-arrow.svg';
	import { afterNavigate, goto } from '$app/navigation';
	import { consoleUrl } from '../../consoleUrl';
	import BlogPlanTag from './BlogPlanTag.svelte';

	const flipDurationMs = 200;
	let dragDisabled = $state(true);

	let items = $state($blogListStore);
	$effect(() => {
		items = $blogListStore;
	});

	let currentBlogId = $derived($blogStore ? $blogStore.id : $blogListStore[0]?.id);
	let focusedId = $state<number | null>(null);

	function saveOrder(newItems: BlogList[]) {
		blogListStore.set(newItems);
		saveSort(newItems.map((blog) => blog.id));
	}

	function handleConsider(e: any) {
		const {
			items: newItems,
			info: { source, trigger }
		} = e.detail;
		items = newItems;
		// Ensure dragging is stopped on drag finish via keyboard
		if (source === SOURCES.KEYBOARD && trigger === TRIGGERS.DRAG_STOPPED) {
			dragDisabled = true;
		}
	}
	function handleFinalize(e: any) {
		const {
			items: newItems,
			info: { source }
		} = e.detail;
		items = newItems;
		saveOrder(newItems);
		// Ensure dragging is stopped on drag finish via pointer (mouse, touch)
		if (source === SOURCES.POINTER) {
			dragDisabled = true;
		}
	}
	function startDrag(e: any) {
		// preventing default to prevent lag on touch devices (because of the browser checking for screen scrolling)
		e.preventDefault();
		dragDisabled = false;
	}
	function handleKeyDown(e: any) {
		if ((e.key === 'Enter' || e.key === ' ') && dragDisabled) dragDisabled = false;
	}

	let currentDragger: HTMLButtonElement | null = null;
	let dragNoteEl: HTMLSpanElement;

	function handleMouseEnter(e: MouseEvent) {
		currentDragger = e.target as HTMLButtonElement;
	}
	function handleMouseLeave() {
		currentDragger = null;
	}

	$effect(() => {
		positionDragNote();
	});

	function positionDragNote() {
		if (!dragNoteEl) return;

		if (!currentDragger || !dragDisabled || items.length < 2) {
			dragNoteEl.style.display = 'none';
			return;
		}

		const { top, left, height } = currentDragger.getBoundingClientRect();

		dragNoteEl.style.top = `${top + height / 2}px`;
		dragNoteEl.style.left = `${left + 15}px`;
		dragNoteEl.style.display = 'block';
	}

	function selectBlog(blog: BlogList) {
		$blogSelectorOpenStore = false;
		goto(consoleUrl(blog.subdomain));
	}

	function rowEl(id: number | null) {
		if (id === null) return null;
		return document.querySelector(`[data-blog-row-id="${id}"]`) as HTMLElement | null;
	}

	function handleWindowKeydown(e: KeyboardEvent) {
		if (!dragDisabled) return;

		const activeId = focusedId ?? currentBlogId ?? null;

		if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
			e.preventDefault();

			if (items.length === 0) return;

			const ids = items.map((blog) => blog.id);
			const firstId: number = ids[0] as number;
			const currentIndex = ids.indexOf(activeId ?? firstId);
			const delta = e.key === 'ArrowDown' ? 1 : -1;
			const nextIndex = Math.min(Math.max(currentIndex + delta, 0), ids.length - 1);

			focusedId = (ids[nextIndex] as number) ?? null;
			rowEl(focusedId)?.scrollIntoView({ block: 'nearest' });
		} else if (e.key === 'Enter') {
			const blog = items.find((blog) => blog.id === activeId);
			if (blog) {
				e.preventDefault();
				selectBlog(blog);
			}
		}
	}

	afterNavigate(() => {
		$blogSelectorOpenStore = false;
	});

	$effect(() => {
		if ($blogSelectorOpenStore) {
			focusedId = currentBlogId ?? null;
		}
	});
</script>

<svelte:window onkeydown={$blogSelectorOpenStore ? handleWindowKeydown : undefined} />

<Modal size="medium" bind:show={$blogSelectorOpenStore} id="blog-selector-modal">
	{#snippet title()}
		<span class="title">Select a blog</span>
	{/snippet}

	<div
		class="blogs-list"
		use:dndzone={{
			items,
			dragDisabled,
			flipDurationMs,
			dropTargetStyle: {
				outline: 'none',
				background: 'var(--hover)'
			}
		}}
		onfinalize={handleFinalize}
		onconsider={handleConsider}
	>
		{#each items as blog (blog.id)}
			<div
				role="button"
				tabindex={0}
				class="blog-row"
				class:focused={focusedId === blog.id}
				data-blog-row-id={blog.id}
				animate:flip={{ duration: flipDurationMs }}
				onmouseenter={() => (focusedId = blog.id)}
				onclick={() => selectBlog(blog)}
				onkeydown={(e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						selectBlog(blog);
					}
				}}
			>
				{#if items.length > 1}
					<button
						type="button"
						class="dragger"
						style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
						tabindex={dragDisabled ? 0 : -1}
						aria-label="drag-handle"
						onmousedown={startDrag}
						ontouchstart={startDrag}
						onkeydown={handleKeyDown}
						onmouseenter={handleMouseEnter}
						onmouseleave={handleMouseLeave}
						onclick={(e) => e.stopPropagation()}
					>
						<IconGripVertical />
					</button>
				{/if}

				<div class="left">
					<div class="name">
						{blog.name}
						<BlogPlanTag {blog} />
					</div>
					<div class="url">
						<span>
							{blog.url.replace(/https?:\/\//, '')}
						</span>
					</div>
				</div>

				<div class="right">
					<div class="metadata">
						<span>
							{blog.posts_count}
							{blog.posts_count === 1 ? 'post' : 'posts'}
						</span>
					</div>
				</div>
			</div>
		{/each}
	</div>

	<div class="footer">
		<Button as="a" href="/console/new">
			Create a new blog
			{#snippet end()}
				<IconPlus />
			{/snippet}
		</Button>
	</div>
</Modal>

<span class="drag-note" bind:this={dragNoteEl}>
	<img src={arrowSvg} class="arrow" alt="drag arrow" />
	<span class="note"> Drag to reorder </span>
</span>

<style>
	.title {
		font-size: 1.2em;
		font-weight: 600;
	}

	.footer {
		padding-top: 20px;
		display: flex;
		justify-content: center;
	}

	.dragger {
		padding: 0 5px;
		margin-right: 10px;
		position: relative;
		display: inline-flex;
	}

	.drag-note {
		position: fixed;
		display: none;
		z-index: 10000001;
	}

	.arrow {
		position: absolute;
		top: 100%;
		right: 100%;
		margin-right: -10px;
		margin-top: -2px;
		width: 40px;
	}
	.note {
		position: absolute;
		top: 100%;
		right: 100%;
		margin-right: 30px;
		margin-top: 18px;
		font-size: 0.8rem;
		color: var(--text-light);
		width: 100px;
	}

	.blogs-list {
		max-height: 50vh;
		overflow-y: auto;
	}
	.blog-row {
		width: 100%;
		padding: 10px 15px;
		display: flex;
		align-items: center;
		cursor: pointer;
		font-family: inherit;
		text-align: left;
		border-radius: var(--box-radius);
	}
	.blog-row:hover,
	.blog-row.focused {
		background: var(--hover);
	}
	.blog-row .left {
		width: 50%;
	}
	.right {
		width: 50%;
		display: flex;
		align-items: center;
		justify-content: flex-end;
	}
	.metadata {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		font-size: 14px;
		color: var(--text-light);
	}
	.name {
		font-weight: 600;
	}
	.url {
		font-size: 0.9rem;
		color: var(--text-light);
	}
</style>
