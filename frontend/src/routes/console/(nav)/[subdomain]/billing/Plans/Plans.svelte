<script lang="ts">
	import { getConfig } from '../../../../lib/config';
	import { onMount } from 'svelte';

	let wrap: HTMLDivElement = $state();

	onMount(() => {
		const script = document.createElement('script');
		script.src = getConfig().hyvor.instance + '/js/billing-component-iframe.js?component=blogs';
		wrap.appendChild(script);

		return () => {
			wrap.removeChild(script);
		};
	});
</script>

<div class="content" bind:this={wrap}></div>

<!-- <style>
	.title {
		margin-bottom: 15px;
		display: flex;
		align-items: center;
	}
	.title .left {
		font-size: 20px;
		font-weight: 600;
		flex: 1;
	}

	.yearly {
		font-size: 14px;
		font-weight: 500;
		color: var(--text-light);
		position: relative;
		margin-right: 25px;
	}

	.yearly-tag {
		position: absolute;
		bottom: 100%;
		margin-bottom: -1px;
		right: -20px;
	}

	.section-desc {
		font-size: 14px;
		text-align: center;
		padding: 10px;
		color: var(--text-light);
	}
</style> -->
