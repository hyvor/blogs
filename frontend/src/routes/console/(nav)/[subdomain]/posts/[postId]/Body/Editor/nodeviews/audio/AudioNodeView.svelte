<script lang="ts">
	import { IconButton, Loader, Tooltip, confirm } from '@hyvor/design/components';
	import type { EditorView } from 'prosemirror-view';
	import { IconPencil, IconTrash } from '@hyvor/icons';
	import { onMount } from 'svelte';
	import FileUploader from '../../../../../../../../lib/components/FileUploader/FileUploader.svelte';

	export let src: string;
	export let getPos: () => number | undefined;
	export let view: EditorView;

	let audioEl: HTMLAudioElement;
	let fileInputEl: HTMLInputElement;

	let loading = false;

	function updateProps(
		props: Partial<{
			src?: string | Blob;
		}>
	) {
		const pos = getPos();
		if (pos === undefined) return;

		view.dispatch(
			view.state.tr.setNodeMarkup(pos, undefined, {
				...{
					src
				},
				...props
			})
		);
	}

	function handleChangeClick() {
		const div = document.createElement('div');
		document.body.appendChild(div);

		const selector = new FileUploader({
			target: div,
			props: {
				type: 'audio'
			}
		});

		function destroy() {
			selector.$destroy();
			div.remove();
		}

		selector.$on('close', () => {
			destroy();
		});

		selector.$on('select', (e) => {
			destroy();
			updateProps({
				src: e.detail.url
			});
		});
	}

	async function handleDelete() {
		if (
			await confirm({
				title: 'Remove audio',
				content: 'Are you sure you want to remove this audio?',
				confirmText: 'Yes, remove it',
				danger: true
			})
		) {
			deleteNodeAudio();
		}
	}

	function deleteNodeAudio() {
		const { tr } = view.state;
		const pos = getPos();
		if (pos === undefined) return;
		tr.delete(pos, pos + 1);
		view.dispatch(tr);
		view.focus();
	}

	onMount(() => {
		if (!src) {
			fileInputEl.click(); // Trigger the file input if no audio is selected initially
		}
	});
</script>

<div class="audio-wrap">
	{#if src}
		<div class="audio-actions">
			<Tooltip text="Change audio">
				<IconButton size="small" color="input" on:click={handleChangeClick}>
					<IconPencil size={12} />
				</IconButton>
			</Tooltip>

			<Tooltip text="Remove audio">
				<IconButton size="small" color="input" on:click={handleDelete}>
					<IconTrash size={12} />
				</IconButton>
			</Tooltip>
		</div>
		<div>
			{#if loading}
				<p><Loader /></p>
			{:else if src}
				<audio {src} bind:this={audioEl} controls />
			{:else}
				<p>No audio selected.</p>
			{/if}
		</div>
	{/if}
</div>

<style>
	.audio-wrap {
		display: flex;
		flex-direction: column;
	}
	.audio-actions {
		position: relative;
		align-self: flex-end;
		margin-bottom: 5px;
	}
	audio {
		width: 100%;
	}
</style>
