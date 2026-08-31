<script lang="ts">
	import { Button, Loader, toast } from '@hyvor/design/components';
	import type {
		ExcalidrawImperativeAPI,
		ExcalidrawInitialDataState
	} from '@excalidraw/excalidraw/types/types.js';
	import { browser } from '$app/environment';
	import { exportToBlob, exportToCanvas, exportToSvg } from '@excalidraw/excalidraw';
	import { createEventDispatcher, onMount } from 'svelte';
	import type { SelectedFile } from '../image-uploader';
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import IconArrowRightCircle from '@hyvor/icons/IconArrowRightCircle';
	import IconSendFill from '@hyvor/icons/IconSendFill';
	import { getI18n } from '../../../i18n';

	const i18n = getI18n();

	if (browser) (window as any).process = { env: { IS_PREACT: false } };

	interface Props {
		initialData?: ExcalidrawInitialDataState;
	}

	let { initialData = {} }: Props = $props();

	let excalidrawAPI: ExcalidrawImperativeAPI | undefined = $state();

	const dispatch = createEventDispatcher<{
		select: SelectedFile;
	}>();

	async function handleFinish() {
		if (!excalidrawAPI) {
			return toast.error(i18n.t('console.fileUploader.excalidrawNotReady'));
		}
		const elements = excalidrawAPI.getSceneElements();

		if (!elements || !elements.length) {
			return toast.error(i18n.t('console.fileUploader.noElementsFound'));
		}

		const svg = await exportToSvg({
			elements,
			appState: excalidrawAPI.getAppState(),
			files: excalidrawAPI.getFiles()
		});

		let data = new XMLSerializer().serializeToString(svg);
		data = data.replace('excalidraw@undefined', 'excalidraw@0.17.2');

		const blob = new Blob([data], {
			type: 'image/svg+xml;charset=utf-8'
		});

		/* const blob = await exportToBlob({
            elements,
            mimeType: "image/svg+xml",
            appState: excalidrawAPI.getAppState(),
            files: excalidrawAPI.getFiles(),
            quality: 1
        }); */

		/* const canvas = await exportToCanvas({
            elements,
            appState: excalidrawAPI.getAppState(),
            files: excalidrawAPI.getFiles(),
        }); */

		dispatch('select', {
			type: 'image',
			url: blob,
			from: 'excalidraw',
			excalidraw: {
				elements,
				appState: excalidrawAPI.getAppState()
			}
		});
	}
</script>

{#await import('./ExcalidrawComponent.svelte')}
	<Loader full />
{:then { default: ExcalidrawComponent }}
	<div class="display">
		<ExcalidrawComponent bind:excalidrawAPI {initialData} />
		<div class="footer">
			<Button size="large" on:click={handleFinish}>
				Finalize {#snippet end()}
					<IconArrowRightCircle />
				{/snippet}
			</Button>
		</div>
	</div>
{/await}

<style>
	.display {
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	.footer {
		padding-top: 15px;
		margin-bottom: 10px;
		text-align: center;
	}
</style>
