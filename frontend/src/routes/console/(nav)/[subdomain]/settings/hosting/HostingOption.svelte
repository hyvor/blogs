<script lang="ts">
	import { Button, Tag } from '@hyvor/design/components';

	interface Props {
		title: string;
		subtitle: string;
		active: boolean;
		buttonLabel: string;
		onclick: () => void;
		tag?: {
			color: 'green' | 'orange' | 'blue' | 'red' | 'default' | 'accent';
			label: string;
		} | null;
	}

	let { title, subtitle, active, buttonLabel, onclick, tag = null }: Props = $props();
</script>

<div class="hosting-option" class:active>
	<div class="hosting-option-header">
		<span class="hosting-option-title">{title}</span>
		{#if active}
			<Tag color="green" size="small">Active</Tag>
		{:else if tag}
			<Tag color={tag.color} size="small">{tag.label}</Tag>
		{/if}
	</div>
	<p class="hosting-option-subtitle">{subtitle}</p>

	<div class="button-wrap">
		<Button size="small" variant="outline" disabled={active} {onclick}>{buttonLabel}</Button>
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
	}
	.hosting-option-title {
		font-weight: 600;
		margin-bottom: 6px;
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
