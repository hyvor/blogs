<script lang="ts">
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import { getPurifiedHtmlFromMarkdown } from './html';
	import { slide } from 'svelte/transition';
	import type { AiMessageEvent } from '../../../../lib/types';

	interface Props {
		event: AiMessageEvent;
	}

	let { event }: Props = $props();

	let hasContent = $derived(event.content && event.content.trim() !== '');

	let open = $state(false);

	function handleClick() {
		if (!hasContent) return;
		open = !open;
	}
</script>

<div class="step-block">
	<button type="button" class="step-header" onclick={handleClick}>
		<IconLightbulb size={11} />
		<span>Thought process</span>

		{#if hasContent}
			<span class="chevron" class:open>
				<IconChevronDown size={10} />
			</span>
		{/if}
	</button>
	{#if open}
		<div class="step-content" transition:slide={{ duration: 150 }}>
			{@html getPurifiedHtmlFromMarkdown(event.content!)}
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

	.step-content :global(p) {
		margin: 6px 0;
	}

	.step-content :global(p:first-child) {
		margin-top: 0;
	}
</style>
