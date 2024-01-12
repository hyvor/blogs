<script lang="ts">
	import { Button, Loader, toast } from "@hyvor/design/components";
    import type { ExcalidrawImperativeAPI, ExcalidrawInitialDataState } from "@excalidraw/excalidraw/types/types.js";
	import { browser } from "$app/environment";
	import { exportToBlob, exportToCanvas, exportToSvg } from "@excalidraw/excalidraw";
	import { createEventDispatcher, onMount } from "svelte";
	import type { SelectedImage } from "../image-uploader";
	import { IconArrowRight, IconArrowRightCircle, IconSendFill } from "@hyvor/icons";

    if(browser)
		window.process = {env: {IS_PREACT: false}};

    export let initialData : ExcalidrawInitialDataState = {};

	let excalidrawAPI: ExcalidrawImperativeAPI;

    const dispatch = createEventDispatcher<{
        select: SelectedImage
    }>();

    async function handleFinish() {
        if (!excalidrawAPI) {
            return toast.error("Excalidraw is not ready yet");
        }
        const elements = excalidrawAPI.getSceneElements();

        if (!elements || !elements.length) {
            return toast.error('No elements found');
        }

        const svg = await exportToSvg({
            elements,
            appState: excalidrawAPI.getAppState(),
            files: excalidrawAPI.getFiles(),
        });

        let data = (new XMLSerializer()).serializeToString(svg);
        data = data.replace('excalidraw@undefined', 'excalidraw@0.17.2')

        console.log(data);
        const blob = new Blob([data], { 
            type: "image/svg+xml;charset=utf-8" 
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
            url: URL.createObjectURL(blob),
            name: null,
            size: blob.size,
            from: 'excalidraw',
            excalidraw: {
                elements,
                appState: excalidrawAPI.getAppState(),
            }
        })
    }

</script>

{#await import('./ExcalidrawComponent.svelte')}
    <Loader full />
{:then { default: ExcalidrawComponent }}
    <div class="display">
        <svelte:component 
            this={ExcalidrawComponent}
            bind:excalidrawAPI={excalidrawAPI}
            initialData={initialData}
        />
        <div class="footer">
            <Button 
                size="large"
                on:click={handleFinish}
            >
                Finalize <IconArrowRightCircle slot="end" />
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