<script lang="ts">
	import { Button, Loader, toast } from "@hyvor/design/components";
    import type { ExcalidrawImperativeAPI, ExcalidrawInitialDataState } from "@excalidraw/excalidraw/types/types.js";
	import { browser } from "$app/environment";
	import { exportToSvg } from "@excalidraw/excalidraw";
	import { createEventDispatcher, onMount } from "svelte";
	import type { SelectedImage } from "../image-uploader";
	import { IconSendFill } from "@hyvor/icons";

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
        const blob = new Blob([svg.outerHTML], { type: "image/svg+xml" });

        dispatch('select', {
            url: URL.createObjectURL(blob),
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
                Finish and Add <IconSendFill slot="end" />
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