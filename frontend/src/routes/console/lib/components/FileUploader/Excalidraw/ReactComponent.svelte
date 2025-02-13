<script lang="ts">
	import * as React from 'react';
	import { createRoot } from 'react-dom/client';
	import { onDestroy } from 'svelte';

	let container: HTMLElement;

	let root: ReturnType<typeof createRoot>;

	let componentProps = $props();

	$effect(() => {
		root = createRoot(container);
		const { this: component, children, ...props } = componentProps;

		root.render(React.createElement(component, props, children));
	});

	onDestroy(() => {
		root.unmount();
	});
</script>

<div class="reactComponent" bind:this={container}></div>

<style>
	.reactComponent {
		height: 100%;
		width: 100%;
	}
</style>
