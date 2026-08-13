<script lang="ts">
	import { Button, Tag } from '@hyvor/design/components';
	import type { Snippet } from 'svelte';

	interface Props {
		title: string;
		subtitle: string | Snippet;
		active: boolean;
		buttonLabel: string;
		buttonDisabled?: boolean;
		onclick: () => void;
		tag?: {
			color: 'green' | 'orange' | 'blue' | 'red' | 'default' | 'accent';
			label: string;
		} | null;
	}

	let {
		title,
		subtitle,
		active,
		buttonLabel,
		buttonDisabled = false,
		onclick,
		tag = null
	}: Props = $props();
</script>

<div class="hosting-option" class:active>
	<div class="hosting-option-header">
		<span class="hosting-option-title">{title}</span>

		<span>
			{#if active}
				<Tag color="green" size="small">Active</Tag>
			{:else if tag}
				<Tag color={tag.color} size="small">{tag.label}</Tag>
			{/if}
		</span>
	</div>
	<p class="hosting-option-subtitle">
		{#if typeof subtitle === 'string'}
			{subtitle}
		{:else}
			{@render subtitle()}
		{/if}
	</p>

	<div class="button-wrap">
		<Button size="small" variant="outline" disabled={active || buttonDisabled} {onclick}
			>{buttonLabel}</Button
		>
	</div>
</div>

<style>
	.hosting-option {
		border: 2px solid var(--border);
		border-radius: var(--box-radius, 8px);
		padding: 16px;
		display: flex;
		flex-direction: column;
	}
	.hosting-option.active {
		border-color: var(--accent);
	}
	.hosting-option-header {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 6px;
	}
	.hosting-option-title {
		font-weight: 600;
		line-height: 1;
	}
	.hosting-option-subtitle {
		font-size: 14px;
		color: var(--text-light);
		margin: 0;
		flex: 1;
	}
	.button-wrap {
		margin-top: 16px;
	}
</style>
