<script lang="ts">
	import type { FormEventHandler } from 'svelte/elements';
	import {
		postEditor,
		postVariantOriginalStore,
		postVariantStore,
		updatePostVariantStore
	} from '../../postStore';
	import { onMount, tick } from 'svelte';
	import { TextSelection } from 'prosemirror-state';
	import UnsavedTag from '../Sidebar/Settings/UnsavedTag.svelte';
	import { updatePostVariant } from '../../postActions';
	import { getI18n } from '../../../../../lib/i18n';
	import { cant } from '../../../../../lib/scope.svelte';

	const i18n = getI18n();

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

	export function focusAtEnd() {
		textarea?.focus();
		const length = textarea?.value.length ?? 0;
		textarea?.setSelectionRange(length, length);
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
			return;
		}

		if (e.key === 'ArrowRight') {
			const target = e.currentTarget as HTMLTextAreaElement;
			const atEnd =
				target.selectionStart === target.value.length &&
				target.selectionEnd === target.value.length;
			if (!atEnd) return;

			e.preventDefault();
			e.stopPropagation();
			focusEditorAtStart();
		}
	}

	function focusEditorAtStart() {
		const editor = $postEditor;
		const view = editor?.getView();
		if (!view) return;

		const selection = TextSelection.atStart(view.state.doc);
		view.dispatch(view.state.tr.setSelection(selection));
		view.focus();
	}

	onMount(() => {
		if (!textarea) return;
		setTimeout(handleResize, 0);
		setTimeout(handleResize, 100); // give time to load fonts and stuff
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
		placeholder={i18n.t('console.postEditor.titlePlaceholder')}
		value={$postVariantStore.title}
		onkeydown={handleKeydown}
		oninput={handleInput}
		bind:this={textarea}
		onblur={handleBlur}
		name="title"
		readonly={cant('posts.write')}></textarea>

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
		width: 700px;
		margin: auto;
	}

	textarea {
		font-family: var(--font-serif);
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
		padding-top: 15px;
		padding-bottom: 0px;
		color: var(--text-faded);
		line-height: 1.1;
	}

	.unsaved-tag {
		position: absolute;
		top: 0;
		left: 0;
		margin-top: -8px;
	}
</style>
