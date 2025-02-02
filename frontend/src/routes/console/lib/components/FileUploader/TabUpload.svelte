<script lang="ts">
	import { Button, Loader, TextInput, toast } from '@hyvor/design/components';
	import IconArrowReturnLeft from '@hyvor/icons/IconArrowReturnLeft';
	import { createEventDispatcher, onMount } from 'svelte';
	import { VALID_MIME_TYPES, type SelectedFile, VALID_MIME_TYPES_NAMES } from './image-uploader';
	import { VALID_MIME_TYPES_AUDIO, VALID_MIME_TYPES_NAMES_AUDIO } from './audio-uploader';
	import { isValidUrl } from '../../helper/is-valid-url';
	import { getConfig } from '../../config';
	import byteFormatter from '../../helper/byte-formatter';

	interface Props {
		isUploading?: boolean;
		type?: 'image' | 'audio' | 'any';
	}

	let { isUploading = $bindable(false), type = 'image' }: Props = $props();

	let inputEl: HTMLInputElement;
	let byUrlInputEl: HTMLInputElement | undefined = $state();

	let byUrl = $state('');
	let isDragging = $state(false);

	function getCtrl() {
		const platform =
			(navigator as any)?.userAgentData?.platform || navigator?.platform || 'unknown';
		return platform.match(/mac/i) ? '⌘' : 'Ctrl';
	}

	const dispatch = createEventDispatcher<{ select: SelectedFile }>();

	function getSelectedType(blob: Blob | null = null): 'image' | 'audio' | 'other' {
		if (type === 'any' && blob) {
			if (blob.type.indexOf('image') === 0) return 'image';
			if (blob.type.indexOf('audio') === 0) return 'audio';
		}
		return type === 'any' ? 'other' : type;
	}

	function handleFetch() {
		isUploading = true;

		fetch(byUrl)
			.then((res) => res.blob())
			.then((blob) => {
				// check if valid image or audio
				if (type === 'image' && blob.type.indexOf('image') !== 0) {
					toast.error('The URL is not an image');
					return;
				}

				if (type === 'audio' && blob.type.indexOf('audio') !== 0) {
					toast.error('The URL is not an audio');
					return;
				}

				dispatch('select', {
					type: getSelectedType(blob),
					url: blob,
					from: 'upload',
					upload: {
						type: 'url',
						originalUrl: byUrl
					}
				});
			})
			.catch((err) => {
				toast.error(type === 'audio' ? 'Failed to fetch audio' : 'Failed to fetch image');
			})
			.finally(() => {
				isUploading = false;
			});
	}

	function handlePaste(e: ClipboardEvent) {
		if (type === 'audio') return;

		const text = e.clipboardData?.getData('text/plain') || '';

		if (isValidUrl(text) && (e.target as HTMLElement).tagName !== 'INPUT') {
			byUrl = text;
			handleFetch();
			return;
		}

		const items = e.clipboardData?.items;
		if (!items) return;

		for (let i = 0; i < items.length; i++) {
			const item = items[i]!;
			if (item.type.indexOf('image') === 0) {
				const blob = item.getAsFile();
				if (!blob) continue;
				dispatch('select', {
					type: getSelectedType(blob),
					url: blob,
					from: 'upload',
					upload: { type: 'paste' }
				});
				break;
			}
		}
	}

	function handleDragEnter(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();
		isDragging = true;
	}

	function handleDragLeave(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();
		isDragging = false;
	}

	function handleDragDrop(e: DragEvent) {
		e.preventDefault();
		e.stopPropagation();

		isDragging = false;

		if (!e.dataTransfer) return;
		const files = e.dataTransfer.files;
		const file = getFileFromFiles(files);
		if (!file) return;

		dispatch('select', {
			type: getSelectedType(file),
			url: file,
			from: 'upload',
			upload: { type: 'dnd' }
		});
	}

	function handleUploadClick() {
		inputEl?.click();
	}

	function handleInputChange(e: any) {
		const file = getFileFromFiles(e.target.files);
		if (!file) return;

		dispatch('select', {
			type: getSelectedType(file),
			url: file,
			from: 'upload',
			upload: { type: 'browse' }
		});
	}

	function getFileFromFiles(files: FileList | null): File | null {
		if (!files || files.length === 0) {
			toast.error('No files selected');
			return null;
		}
		const file = files[0];
		if (!file) {
			toast.error('No files selected');
			return null;
		}

		const max = getConfig().limits.max_upload_size;
		if (file.size > max) {
			toast.error('File size exceeds the limit of ' + byteFormatter(max));
			return null;
		}

		if (type === 'image' && !VALID_MIME_TYPES.includes(file.type)) {
			const names = VALID_MIME_TYPES_NAMES.join(', ').toUpperCase();
			toast.error(`Only ${names} images are allowed`);
			return null;
		}

		if (type === 'audio' && !VALID_MIME_TYPES_AUDIO.includes(file.type)) {
			const names = VALID_MIME_TYPES_NAMES_AUDIO.join(', ').toUpperCase();
			toast.error(`Only ${names} audio are allowed`);
			return null;
		}

		// const url = URL.createObjectURL(file);

		return file;
	}

	onMount(() => {
		byUrl && byUrlInputEl && byUrlInputEl.focus();
	});
</script>

<svelte:window
	onpaste={handlePaste}
	ondragenter={handleDragEnter}
	ondragover={handleDragEnter}
	ondragleave={handleDragLeave}
	ondragexit={handleDragLeave}
/>

<div class="tab">
	<input
		type="file"
		accept={type === 'audio' ? 'audio/*' : type === 'image' ? 'image/*' : '*'}
		style="display:none"
		bind:this={inputEl}
		onchange={handleInputChange}
	/>

	{#if isUploading}
		<Loader full />
	{:else}
		<div class="upload-wrap">
			<div
				class="upload-area"
				onclick={handleUploadClick}
				ondrop={handleDragDrop}
				role="button"
				tabindex="0"
				onkeyup={(e) => e.key === 'Enter' && handleUploadClick()}
			>
				{#if isDragging}
					Drop here!
				{:else}
					Drag and drop, paste ({getCtrl()} + v), or click to upload
				{/if}
			</div>
		</div>

		{#if type === 'image'}
			<div class="by-url-wrap">
				<div class="title">or, Upload by URL</div>

				<div class="input-button">
					<TextInput
						block
						placeholder="Enter image URL"
						bind:value={byUrl}
						on:keyup={(e) => e.key === 'Enter' && handleFetch()}
						bind:input={byUrlInputEl}
					/>
					<Button disabled={byUrl.trim() === ''} on:click={handleFetch}>
						Fetch {#snippet end()}
							<IconArrowReturnLeft />
						{/snippet}
					</Button>
				</div>
			</div>
		{/if}

		<!-- <div
            class="upload-area" 
            onClick={() => inputRef.current && inputRef.current.click()}
            ref={uploadAreaRef}
        >
            {
                isDragging ?
                "Drop here!" :
                "Drag and drop, paste, or click to upload"
            }
        </div> -->
	{/if}
</div>

<style lang="scss">
	.tab {
		height: 100%;
		display: flex;
		flex-direction: column;
		padding-bottom: 15px;
	}

	.upload-wrap {
		flex: 1;
		width: 100%;
		height: 100%;
		.upload-area {
			background-color: var(--input);
			width: 100%;
			height: 100%;
			border-radius: 20px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 14px;
			color: var(--text-light);
			transition: 0.2s box-shadow;
			cursor: pointer;
			&:hover {
				box-shadow: 0 0 0 2px var(--accent-light);
			}
		}
	}

	.by-url-wrap {
		margin-top: 15px;
		.title {
			font-size: 0.9rem;
			font-weight: 500;
			color: var(--text-light);
			margin-bottom: 10px;
			padding-left: 5px;
			text-align: center;
		}
		.input-button {
			display: flex;
			align-items: center;
			gap: 10px;
		}
	}
</style>
