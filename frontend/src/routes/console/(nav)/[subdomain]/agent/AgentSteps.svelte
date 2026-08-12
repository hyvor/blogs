<script lang="ts">
	import { Loader } from '@hyvor/design/components';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconWrench from '@hyvor/icons/IconWrench';
	import IconCheck from '@hyvor/icons/IconCheck';
	import type { AgentBlock } from './agentApi';

	interface Props {
		blocks: AgentBlock[];
	}

	let { blocks }: Props = $props();

	// tracks user-toggled thinking blocks, keyed by block index
	let openThinking: Record<number, boolean> = $state({});

	function isThinkingOpen(i: number, done: boolean) {
		return openThinking[i] ?? !done;
	}

	function toggleThinking(i: number, done: boolean) {
		openThinking[i] = !isThinkingOpen(i, done);
	}

	function toolLabel(name: string) {
		return name.replace(/[_-]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
	}
</script>

<div class="steps">
	{#each blocks as block, i (i)}
		{#if block.type === 'thinking'}
			<div class="step thinking-step">
				<button type="button" class="step-header" onclick={() => toggleThinking(i, block.done)}>
					<IconLightbulb size={13} />
					<span>{block.done ? 'Thought' : 'Thinking…'}</span>
					{#if !block.done}
						<Loader size="small" />
					{/if}
					<span class="chevron" class:open={isThinkingOpen(i, block.done)}>
						<IconChevronDown size={12} />
					</span>
				</button>
				{#if isThinkingOpen(i, block.done)}
					<div class="thinking-content">{block.content}</div>
				{/if}
			</div>
		{:else if block.type === 'tool'}
			<div class="step tool-step">
				<IconWrench size={13} />
				<span class="tool-name">{toolLabel(block.name)}</span>
				{#if block.status === 'running'}
					<Loader size="small" />
				{:else}
					<span class="tool-done"><IconCheck size={13} /></span>
				{/if}
			</div>
		{/if}
	{/each}
</div>

<style lang="scss">
	.steps {
		display: flex;
		flex-direction: column;
		gap: 6px;
		margin-bottom: 10px;
	}

	.step {
		font-size: 12px;
		color: var(--text-light);
	}

	.thinking-step .step-header {
		display: flex;
		align-items: center;
		gap: 6px;
		background: none;
		border: none;
		padding: 0;
		margin: 0;
		font: inherit;
		color: inherit;
		cursor: pointer;
	}

	.thinking-step .chevron {
		display: inline-flex;
		transition: transform 0.15s ease;
	}

	.thinking-step .chevron.open {
		transform: rotate(180deg);
	}

	.thinking-content {
		margin-top: 4px;
		padding: 8px 10px;
		border-left: 2px solid var(--border);
		font-size: 12px;
		line-height: 1.5;
		color: var(--text-light);
		white-space: pre-wrap;
	}

	.tool-step {
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.tool-step .tool-name {
		font-weight: 600;
	}

	.tool-step .tool-done {
		display: inline-flex;
		color: var(--green);
	}
</style>
