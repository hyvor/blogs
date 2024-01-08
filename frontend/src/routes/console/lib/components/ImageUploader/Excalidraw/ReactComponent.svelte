<script lang="ts">
	import * as React from "react";
	import { createRoot } from "react-dom/client";
	import { afterUpdate, onDestroy } from "svelte";

	let container: HTMLElement;

	let root: ReturnType<typeof createRoot>;
	afterUpdate(() => {
		root = createRoot(container);
		const { this: component, children, ...props } = $$props;

		root.render(React.createElement(component, props, children));
	});

	onDestroy(() => {
		root.unmount();
	});
</script>


<div class="reactComponent" bind:this={container} />

<style>
	.reactComponent {
		height: 100%;
		width: 100%;
	}
</style>