<script lang="ts">
	import IconSearch from '@hyvor/icons/IconSearch';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import { slide } from 'svelte/transition';
	import type { AiMessageEvent } from '../../../../lib/types';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		event: AiMessageEvent;
	}

	let { event }: Props = $props();

	let open = $state(false);

	let QUERY_LABELS: Record<string, string> = $derived({
		get_tags: i18n.t('console.agent.query.searchedTags'),
		get_authors: i18n.t('console.agent.query.searchedAuthors'),
		get_post_variants: i18n.t('console.agent.query.searchedPosts'),
		get_languages: i18n.t('console.agent.query.searchedLanguages')
	});

	function queryLabel(toolName: string | null | undefined) {
		if (!toolName) return i18n.t('console.agent.query.ranQuery');
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
			<div class="io-label">{i18n.t('console.agent.query.input')}</div>
			<pre>{prettyJson(event.tool_input)}</pre>
			<div class="io-label">{i18n.t('console.agent.query.output')}</div>
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
