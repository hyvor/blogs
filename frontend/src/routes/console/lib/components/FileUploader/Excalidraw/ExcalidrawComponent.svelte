<script lang="ts">
	import ReactComponent from "./ReactComponent.svelte";
	import { Excalidraw, MainMenu, exportToBlob } from "@excalidraw/excalidraw";
	import type { ExcalidrawElement } from "@excalidraw/excalidraw/types/element/types.js";
	import type {
		AppState,
		ExcalidrawImperativeAPI,
		ExcalidrawInitialDataState
	} from "@excalidraw/excalidraw/types/types.js";
	import React from "react";
	import { createEventDispatcher } from "svelte";

	interface Props {
		initialData?: ExcalidrawInitialDataState;
		excalidrawAPI: ExcalidrawImperativeAPI | undefined;
	}

	let { initialData = {}, excalidrawAPI = $bindable() }: Props = $props();

	const dispatcher = createEventDispatcher<{
		init: void;
		change: { elements: ExcalidrawElement[]; state: AppState };
		blob: Blob;
	}>();

	function onChange(elements: ExcalidrawElement[], state: AppState) {
		dispatcher("change", { elements, state });
	}
	const reactMainMenu = React.createElement(MainMenu, null, [
		React.createElement(MainMenu.DefaultItems.SaveAsImage, { key: "SaveAsImage" }),
		React.createElement(MainMenu.DefaultItems.Export, { key: "Export" }),
		React.createElement(MainMenu.DefaultItems.ClearCanvas, { key: "Clear Canvas" }),
		React.createElement(MainMenu.DefaultItems.ChangeCanvasBackground, { key: "Canvas Background" }),
	]);

	function handleExcalidrawApi(api: ExcalidrawImperativeAPI) {
		excalidrawAPI = api;
	}

</script>

<ReactComponent
	{onChange}
	this={Excalidraw}
	{initialData}
	langCode="en-EN"
	children={reactMainMenu}
	excalidrawAPI={handleExcalidrawApi}
/>
