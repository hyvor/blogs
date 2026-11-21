<script lang="ts">
	import { Modal } from '@hyvor/design/components';
	import { onMount } from 'svelte';
	import { getConfig } from '../../config';

	interface Props {
		show: boolean;
		focus: string;
		title: string;
	}

	let { show = $bindable(), focus, title }: Props = $props();

	let wrap: HTMLDivElement | undefined = $state();

	// loads the billing component of HYVOR Core (same as /console/billing),
	// focused on the given license property (plans + add-ons are highlighted there)
	onMount(() => {
		if (!wrap) {
			return;
		}

		const script = document.createElement('script');
		const params = new URLSearchParams({ component: 'blogs', focus });
		script.src = getConfig().hyvor.instance + '/js/billing-component-iframe.js?' + params;
		wrap.appendChild(script);

		return () => {
			script.parentNode?.removeChild(script);
			wrap?.querySelector('iframe')?.remove();
		};
	});
</script>

<div class="wrap">
	<Modal bind:show {title} size="large">
		<div class="content" bind:this={wrap}></div>
	</Modal>
</div>

<style>
	.content {
		margin: -10px -20px;
	}
</style>
