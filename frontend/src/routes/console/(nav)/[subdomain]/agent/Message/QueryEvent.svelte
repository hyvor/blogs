<script lang="ts">
	import IconSearch from '@hyvor/icons/IconSearch';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import { slide } from 'svelte/transition';
	import type { AiMessageEvent } from '../../../../lib/types';

	interface Props {
		event: AiMessageEvent;
	}

	let { event }: Props = $props();

	let open = $state(false);

	const QUERY_LABELS: Record<string, string> = {
		get_tags: 'Searched tags',
		get_authors: 'Searched authors',
		get_post_variants: 'Searched posts',
		get_languages: 'Searched languages'
	};

	function queryLabel(toolName: string | null | undefined) {
		if (!toolName) return 'Ran a query';
		return QUERY_LABELS[toolName] ?? toolName.replace(/_/g, ' ');
	}

	function prettyJson(value: unknown) {
		if (value === null || value === undefined) return '';
		try {
			return JSON.stringify(value, null, 2);
		} catch {
			return String(value);
		}
	}
</script>

<div class="step-block">
	<button type="button" class="step-header" onclick={() => (open = !open)}>
		<IconSearch size={11} />
		<span>{queryLabel(event.tool_name)}</span>
		<span class="chevron" class:open>
			<IconChevronDown size={10} />
		</span>
	</button>
	{#if open}
		<div class="step-content" transition:slide={{ duration: 150 }}>
			<div class="io-label">Input</div>
			<pre>{prettyJson(event.tool_input)}</pre>
			<div class="io-label">Output</div>
			<pre>{prettyJson(event.tool_output)}</pre>
		</div>
	{/if}
</div>

<style>
	.step-block {
		margin-bottom: 10px;
	}

	.step-header {
		display: flex;
		align-items: center;
		gap: 6px;
		background: none;
		border: none;
		padding: 0;
		margin: 0;
		font: inherit;
		font-size: 12px;
		color: var(--text-light);
		cursor: pointer;
		text-transform: capitalize;
	}

	.chevron {
		display: inline-flex;
		transition: transform 0.15s ease;
	}

	.chevron.open {
		transform: rotate(180deg);
	}

	.step-content {
		margin-top: 4px;
		padding: 2px 10px;
		border-left: 2px solid var(--border);
		font-size: 12px;
		line-height: 1.5;
		color: var(--text-light);
		max-height: 400px;
		overflow: auto;
	}

	.step-content pre {
		margin: 4px 0 10px;
		padding: 8px 10px;
		background-color: var(--box-background);
		border-radius: 6px;
		font-size: 11px;
		line-height: 1.5;
		white-space: pre-wrap;
		word-break: break-word;
	}

	.io-label {
		font-weight: 600;
		text-transform: uppercase;
		font-size: 10px;
		letter-spacing: 0.02em;
		margin-top: 8px;
	}
</style>
