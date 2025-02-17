<script lang="ts">
	import { IconButton, Loader, Tooltip, confirm } from '@hyvor/design/components';
	import type { EditorView } from 'prosemirror-view';
	import IconPencil from '@hyvor/icons/IconPencil';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { onMount } from 'svelte';
	// import FileUploader from '../../../../routes/console/lib/components/FileUploader/FileUploader.svelte';

	interface Props {
		src: string;
		getPos: () => number | undefined;
		view: EditorView;
	}

	let { src, getPos, view }: Props = $props();

	let audioEl: HTMLAudioElement | undefined = $state();
	//let fileInputEl: HTMLInputElement;

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
		// TODO: abstract this
		return;

		/* const div = document.createElement("div");
    document.body.appendChild(div);

    const selector = mount(FileUploader, {
      target: div,
      props: {
        type: "audio",
        onselect: (file: any) => {
          updateProps({
            src: file.url,
          });
          destroy();
        },
        onclose: () => {
          destroy();
        },
      },
    });

    function destroy() {
      unmount(selector);
      div.remove();
    } */
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
			// fileInputEl.click(); // Trigger the file input if no audio is selected initially
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
				<p>
					<Loader />
				</p>
			{:else if src}
				<audio {src} bind:this={audioEl} controls></audio>
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
