<script lang="ts">
	import { onMount } from 'svelte';
	import byteFormatter from '../../../lib/helper/byte-formatter';
	import type { Usage } from '../billingActions';

	interface Props {
		name: string;
		data: Usage;
		bytes?: boolean;
		percent?: boolean;
	}

	let { name, data, bytes = false, percent = false }: Props = $props();

	let isUnlimited = $derived(data.limit === -1);
	let width = $state('0%');
	let percentage = $derived(Math.min(data.limit <= 0 ? 100 : (data.used / data.limit) * 100, 100));

	onMount(() => {
		setTimeout(() => {
			width = percentage + '%';
		}, 200);
	});

	let current = $state(data.used as number | string);
	let limit = $state(data.limit as number | string);
	if (bytes) {
		current = byteFormatter(data.used);
		limit = byteFormatter(data.limit);
	} else if (percent) {
		current = Math.round(data.used) + '%';
	}

	let color = $derived.by(() => {
		if (percentage > 99) {
			return 'var(--red-dark)';
		} else if (percentage > 85) {
			return 'var(--orange-dark)';
		} else {
			return 'var(--accent)';
		}
	});
</script>

<div class="usage-bar">
	<div class="usage-bar-top">
		<div class="usage-name">
			{name}
		</div>
		{#if isUnlimited}
			<div class="usage-number">
				<span class="usage-now">{current.toLocaleString()}</span>
				<span class="usage-full">/ Unlimited</span>
			</div>
		{:else if data.limit > 0}
			<div class="usage-number">
				<span class="usage-now" style:color={color === 'var(--accent)' ? 'var(--text)' : color}
					>{current.toLocaleString()}</span
				>
				{#if !percent}
					<span class="usage-full">/ {limit.toLocaleString()}</span>
				{/if}
			</div>
		{/if}
	</div>

	{#if isUnlimited}
		<div class="usage-bar-bar">
			<div
				class="usage-bar-fill"
				style:width="100%"
				style:background="var(--accent)"
				style:opacity="0.3"
			></div>
		</div>
	{:else if data.limit === 0}
		<div class="feature-not-included">Your license does not include this feature.</div>
	{:else}
		<div class="usage-bar-bar">
			<div class="usage-bar-fill" style:width style:background={color}></div>
		</div>
	{/if}
</div>

<style>
	.usage-bar-top {
		display: flex;

		.usage-name {
			flex: 1;
			font-size: 14px;
		}
		.usage-now {
			margin-right: 4px;
			font-weight: 600;
		}

		.usage-full {
			color: var(--text-light);
			font-size: 12px;
		}
	}

	.usage-bar-bar {
		margin: 6px 0 15px;
		width: 100%;
		height: 15px;
		background: var(--accent-light);
		border-radius: 20px;
		position: relative;
		overflow: hidden;
	}
	.usage-bar-fill {
		position: absolute;
		left: 0;
		top: 0;
		height: 100%;
		background: var(--accent);
		border-radius: 20px;
		transition: 0.3s width ease-out;
	}

	.feature-not-included {
		color: var(--text-light);
		font-size: 12px;
		margin: 5px 0 15px;
	}
</style>
