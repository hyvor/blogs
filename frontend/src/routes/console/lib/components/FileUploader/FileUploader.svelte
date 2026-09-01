<script lang="ts">
	import type { SelectedFile as SelectedFileInterface } from './image-uploader';
	import { Button, Modal, TabNav, TabNavItem } from '@hyvor/design/components';
	import IconCardImage from '@hyvor/icons/IconCardImage';
	import IconCaretLeft from '@hyvor/icons/IconCaretLeft';
	import IconCloudUpload from '@hyvor/icons/IconCloudUpload';

	import TabUpload from './TabUpload.svelte';
	import SelectedFile from './PreviewSelected/SelectedFile.svelte';
	import ExcalidrawIcon from './Excalidraw/ExcalidrawIcon.svelte';
	import Excalidraw from './Excalidraw/Excalidraw.svelte';
	import Unsplash from './Unsplash/Unsplash.svelte';
	import Media from './Media/Media.svelte';
	import { getI18n } from '../../i18n';

	const i18n = getI18n();

	let tab = $state('upload');

	interface Props {
		type?: 'image' | 'audio' | 'any';
		show?: boolean;
		onselect: (type: SelectedFileInterface) => void;
		onclose?: () => void;
	}

	let { type = 'image', show = $bindable(true), onselect, onclose }: Props = $props();
	let backImage: null | SelectedFileInterface = null;
	let selectedFile: null | SelectedFileInterface = $state(null);

	function handleSelect(e: CustomEvent<SelectedFileInterface>) {
		selectedFile = e.detail;
		backImage = null;
	}

	function handleBack() {
		backImage = selectedFile;
		selectedFile = null;
	}

	$effect(() => {
		if (!show) {
			onclose?.();
		}
	});

	function handleFinish(e: CustomEvent<SelectedFileInterface>) {
		onselect(e.detail);
		show = false;
	}
</script>

<div class="image-uploader">
	<Modal bind:show size="large" closeOnEscape={false} closeOnOutsideClick={false}>
		{#snippet title()}
			<div>
				{#if selectedFile}
					<Button on:click={handleBack} color="input">
						{#snippet start()}
							<IconCaretLeft va />
						{/snippet}
						{i18n.t('console.fileUploader.back')}
					</Button>
				{:else if type === 'any'}
					<TabNav>
						<TabNavItem name="upload" active={tab === 'upload'} onclick={() => (tab = 'upload')}>
							{#snippet start()}
								<IconCloudUpload />
							{/snippet}
							{i18n.t('console.theme.upload')}
						</TabNavItem>
					</TabNav>
				{:else}
					<TabNav>
						<TabNavItem name="upload" active={tab === 'upload'} onclick={() => (tab = 'upload')}>
							{#snippet start()}
								<IconCloudUpload />
							{/snippet}
							{i18n.t('console.theme.upload')}
						</TabNavItem>
						<TabNavItem name="media" active={tab === 'media'} onclick={() => (tab = 'media')}>
							{#snippet start()}
								<IconCardImage />
							{/snippet}
							{i18n.t('console.tools.nav.media')}
						</TabNavItem>

						{#if type === 'image'}
							<TabNavItem
								name="unsplash"
								active={tab === 'unsplash'}
								onclick={() => (tab = 'unsplash')}
							>
								{#snippet start()}
									<svg
										role="img"
										width="1em"
										height="1em"
										fill="currentColor"
										viewBox="0 0 24 24"
										xmlns="http://www.w3.org/2000/svg"
										><path d="M7.5 6.75V0h9v6.75h-9zm9 3.75H24V24H0V10.5h7.5v6.75h9V10.5z" /></svg
									>
								{/snippet}
								Unsplash
							</TabNavItem>
							<TabNavItem
								name="excalidraw"
								active={tab === 'excalidraw'}
								onclick={() => (tab = 'excalidraw')}
							>
								{#snippet start()}
									<ExcalidrawIcon />
								{/snippet}
								Excalidraw
							</TabNavItem>
						{/if}
					</TabNav>
				{/if}
			</div>
		{/snippet}
		<div class="body" style:position={selectedFile ? 'relative' : undefined}>
			{#if tab === 'upload'}
				<TabUpload {type} on:select={handleSelect} />
			{:else if tab === 'media' && type !== 'any'}
				<Media {type} on:select={handleSelect} />
			{:else if tab === 'unsplash'}
				<Unsplash on:select={handleSelect} />
			{:else if tab === 'excalidraw'}
				<Excalidraw on:select={handleSelect} />
			{/if}

			{#if selectedFile}
				<SelectedFile file={selectedFile} on:select={handleFinish} />
			{/if}
		</div>
	</Modal>
</div>

<style lang="scss">
	.image-uploader :global(.wrap) {
		z-index: 1000 !important;
	}

	.image-uploader :global(.inner) {
		height: 100%;
		width: 1100px !important;
		display: flex;
		flex-direction: column;
		:global(> .content) {
			flex: 1;
			padding-top: 0;
			min-height: 0;
			display: flex;
			flex-direction: column;
		}
	}

	.body {
		flex: 1;
		min-height: 0;
	}
</style>
