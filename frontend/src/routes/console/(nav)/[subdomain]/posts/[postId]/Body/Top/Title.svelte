<script lang="ts">
	import type { FormEventHandler } from 'svelte/elements';
	import {
		postEditingStatusStore,
		postEditor,
		postVariantOriginalStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../../postStore';
	import { onMount, tick } from 'svelte';
	import UnsavedTag from '../../Sidebar/Settings/UnsavedTag.svelte';
	import { updatePostVariant } from '../../../postActions';

	const handleInput: FormEventHandler<HTMLTextAreaElement> = (event) => {
		updatePostVariantStore({
			title: event.currentTarget.value
		});
	};

	let loaderState: 'none' | 'loading' | 'success' | 'error' = $state('none');

	function handleBlur(e: any) {
		const title = (e.target.value as string).trim();

		if (!hasChanged) return;

		if ($postVariantStore.status === 'draft') {
			loaderState = 'loading';
			updatePostVariant({ title })
				.catch((err) => {
					loaderState = 'error';
				})
				.finally(() => {
					loaderState = 'success';
				});
		}
	}

	let textarea: HTMLTextAreaElement | undefined = $state();

	export function focus() {
		textarea?.focus();
	}

	function handleResize() {
		if (!textarea) return;
		textarea.style.height = '0';
		textarea.style.height = textarea.scrollHeight + 'px';
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter' || e.key === 'ArrowDown') {
			e.preventDefault();
			e.stopPropagation();
			$postEditor.focus();
		}
	}

	onMount(() => {
		if (!textarea) return;
		setTimeout(handleResize, 0);
		textarea.addEventListener('input', handleResize);
		textarea.addEventListener('change', handleResize);
		textarea.addEventListener('focus', handleResize);
	});

	let previousTitle = '';

	postVariantStore.subscribe(async (value) => {
		if (value.title !== previousTitle) {
			previousTitle = value.title || '';
			await tick();
			handleResize();
		}
	});

	let hasChanged = $derived(
		($postVariantStore.title?.trim() || '') !== ($postVariantOriginalStore.title || '')
	);
</script>

<div class="title-wrap">
	<textarea
		placeholder="Title..."
		value={$postVariantStore.title}
		onkeydown={handleKeydown}
		oninput={handleInput}
		bind:this={textarea}
		onblur={handleBlur}
		name="title"
	></textarea>

	<!-- <div class="loader-wrap">
        <Loader state={loaderState} size="small" />
    </div> -->

	<span class="unsaved-tag">
		<UnsavedTag show={hasChanged} {loaderState} />
	</span>
</div>

<style>
	.title-wrap {
		display: flex;
		align-items: center;
		position: relative;
	}

	textarea {
		font-family: var(--font-serif);
		padding-top: 10px;
		padding-bottom: 10px;
		font-size: 34px;
		font-weight: 800;
		outline: none;
		resize: none;
		border: 0;
		display: block;
		width: 100%;
		background: none;
		overflow: hidden;
		position: relative;
		padding-block: 15px;
		color: var(--text-faded);
		line-height: 1.5;
		/**
		* reduce width of textarea (700px)
		*/
		padding-inline: max(0px, calc(((100% - 760px) / 2) + 30px));
	}

	.unsaved-tag {
		position: absolute;
		bottom: 100%;
		left: 30px;
	}
</style>
