<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { authUserStore, blogListStore } from '../lib/stores';
	import IconCaretLeft from '@hyvor/icons/IconCaretLeft';
import IconCaretRight from '@hyvor/icons/IconCaretRight';
import IconGripVertical from '@hyvor/icons/IconGripVertical';

	import { flip } from 'svelte/animate';
	import { dndzone, SOURCES, TRIGGERS } from 'svelte-dnd-action';
	import type { BlogList } from '../lib/types';
	import { saveSort } from '../lib/actions/blogActions';
	import arrowSvg from './drag-note-arrow.svg';
	import { isTempStore } from '../lib/temp';
	import { afterNavigate, goto } from '$app/navigation';
	import BlogPlanTag from './BlogPlanTag.svelte';

	const flipDurationMs = 200;
	let dragDisabled = true;

	let items = $blogListStore;

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

	let previousPage: string = '/';

	afterNavigate(({ from }) => {
		previousPage = from?.url.pathname || previousPage;
	});

	function handleBack() {
		goto(previousPage);
	}

	let currentDragger: HTMLButtonElement | null = null;
	let dragNoteEl: HTMLSpanElement;

	function handleMouseEnter(e: MouseEvent) {
		currentDragger = e.target as HTMLButtonElement;
	}
	function handleMouseLeave() {
		currentDragger = null;
	}

	$: currentDragger, dragDisabled, positionDragNote();

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
</script>

<div class="wrap">
	<div class="selector">
		<span class="back-button">
			<Button variant="outline" size="small" on:click={handleBack}>
				<IconCaretLeft slot="start" size={14} />
				Back
			</Button>
		</span>

		<div class="selector-box">
			<div class="title">Select a blog</div>

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
				on:finalize={handleFinalize}
				on:consider={handleConsider}
			>
				{#each items as blog, i (blog.id)}
					<a
						class="blog-row"
						href="/console/{blog.subdomain}"
						animate:flip={{ duration: flipDurationMs }}
						on:mouseenter={handleMouseEnter}
						on:mouseleave={handleMouseLeave}
					>
						{#if items.length > 1}
							<button
								class="dragger"
								style={dragDisabled ? 'cursor: grab' : 'cursor: grabbing'}
								tabindex={dragDisabled ? 0 : -1}
								aria-label="drag-handle"
								on:mousedown={startDrag}
								on:touchstart={startDrag}
								on:keydown={handleKeyDown}
								on:click={(e) => e.preventDefault()}
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
								<!-- TODO: Add this again (descendant error) -->
								<!-- <a href={blog.url} target="_blank">
									{blog.url.replace(/https?:\/\//, '')}
								</a> -->
							</div>
						</div>

						<div class="right">
							<div class="metadata">
								<span>
									{blog.posts_count}
									{blog.posts_count === 1 ? 'post' : 'posts'}
								</span>
							</div>

							<div class="icon">
								<IconCaretRight />
							</div>
						</div>
					</a>
				{/each}
			</div>

			<div class="footer">
				<Button as="a" href="/console/new" data-sveltekit-reload={$isTempStore}>
					Create a new blog
				</Button>
			</div>
		</div>
	</div>

	<div class="user-account">
		<div class="inner">
			<div class="left">
				<img src={$authUserStore.picture_url} alt="{$authUserStore.name}'s profile picture" />

				<div class="name-username">
					<div class="name">
						{$authUserStore.name}
					</div>
					{#if $authUserStore.username}
						<div class="username">
							@{$authUserStore.username}
						</div>
					{/if}
				</div>
			</div>

			<div>
				<Button as="a" href="/api/auth/logout" variant="fill-light">Logout</Button>
			</div>
		</div>
	</div>
</div>

<span class="drag-note" bind:this={dragNoteEl}>
	<img src={arrowSvg} class="arrow" alt="drag arrow" />
	<span class="note"> Drag to reorder </span>
</span>

<style lang="scss">
	.wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		height: 100vh;
		width: 100%;
	}

	.user-account {
		padding: 15px;
		border-top: 1px solid var(--border);
		width: 100%;
		.inner {
			width: 600px;
			max-width: 100%;
			margin: auto;
			display: flex;
			align-items: center;
		}
	}
	.user-account .left {
		flex: 1;
		display: flex;
		align-items: center;
	}

	.user-account img {
		width: 40px;
		height: 40px;
		border-radius: 50%;
	}
	.name-username {
		display: flex;
		justify-content: center;
		margin-left: 10px;
		flex-direction: column;
	}
	.username {
		font-size: 0.8rem;
		color: var(--text-light);
	}

	.selector {
		width: 600px;
		max-width: 100%;
		flex: 1;
		position: relative;
		margin-top: 60px;
		padding-bottom: 30px;
		min-height: 0;
		display: flex;
		flex-direction: column;
	}
	.selector-box {
		background: var(--box-background);
		box-shadow: var(--box-shadow);
		border-radius: var(--box-radius);
		min-height: 0;
		display: flex;
		flex-direction: column;
	}
	.title {
		padding: 25px;
		font-size: 1.2rem;
		font-weight: 600;
		text-align: center;
		position: relative;
	}
	.back-button {
		position: absolute;
		left: 0;
		bottom: 100%;
		padding: 15px 0;
	}
	.footer {
		padding: 25px;
		display: flex;
		justify-content: center;
	}
	.dragger {
		padding: 0 5px;
		margin-right: 10px;
		position: relative;
		font-family: inherit;
	}

	.drag-note {
		position: fixed;
		display: none;
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
		overflow-y: auto;
		flex: 1;
		min-height: 0;
	}
	.blog-row {
		padding: 10px 20px;
		display: flex;
		align-items: center;
		cursor: pointer;
	}
	.blog-row:hover {
		background: var(--hover);
	}
	.blog-row .left {
		width: 50%;
	}
	.right {
		width: 50%;
		display: flex;
		align-items: center;
	}
	.metadata {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		margin-right: 20px;
		font-size: 14px;
		color: var(--text-light);
	}
	.icon {
		display: flex;
		align-items: center;
	}
	.name {
		font-weight: 600;
	}
	.url {
		font-size: 0.9rem;
		color: var(--text-light);
	}
	.url a:hover {
		text-decoration: underline;
	}
</style>
