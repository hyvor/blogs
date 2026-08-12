<script lang="ts">
	import { Button, ButtonGroup, Loader, toast } from '@hyvor/design/components';
	import UserLogo from './UserLogo.svelte';
	import { appendHtml, copyHtmlToClipboard, getHtmlFromMarkdownResponse } from './ai';
	import IconClipboard from '@hyvor/icons/IconClipboard';
	import IconFileEarmark from '@hyvor/icons/IconFileEarmark';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconWrench from '@hyvor/icons/IconWrench';
	import IconCheck from '@hyvor/icons/IconCheck';

	import { blogStore } from '../../../../../../lib/stores/blogStore';
	import logo from '$lib/img/logo.png';
	import { postEditingStatusStore } from '../../../postStore';
	import type { AgentTurn } from './aiActions';

	interface Props {
		turn: AgentTurn;
	}

	let { turn }: Props = $props();

	let htmlEl: HTMLDivElement | undefined = $state();

	let finalText = $derived(
		turn.blocks
			.filter((b) => b.type === 'text')
			.map((b) => b.content)
			.join('')
	);

	let responseHtml = $derived(getHtmlFromMarkdownResponse(finalText));

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

	function handleCopy() {
		if (!htmlEl) return;
		copyHtmlToClipboard(htmlEl);
		toast.success('Copied to clipboard');
	}

	function addToEditor() {
		if (!htmlEl) return;
		appendHtml($postEditingStatusStore.editorView!, htmlEl.innerHTML);
	}
</script>

<div class="single">
	<div class="message-wrap">
		<UserLogo url={$blogStore.icon_url || $blogStore.logo_url} />
		<div class="message message-input">{turn.prompt}</div>
	</div>

	<div class="message-wrap ai">
		<UserLogo url={logo} ai />
		<div class="message">
			{#if turn.blocks.length === 0}
				{#if turn.status === 'error'}
					<span class="error">{turn.error}</span>
				{:else}
					<Loader size="small" />
				{/if}
			{:else}
				<div class="steps">
					{#each turn.blocks as block, i (i)}
						{#if block.type === 'thinking'}
							<div class="step thinking-step">
								<button
									type="button"
									class="step-header"
									onclick={() => toggleThinking(i, block.done)}
								>
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

				{#if finalText}
					<div class="message-html" bind:this={htmlEl}>
						{@html responseHtml}
					</div>
				{:else if turn.status === 'streaming'}
					<Loader size="small" />
				{/if}

				{#if turn.status === 'error'}
					<span class="error">{turn.error}</span>
				{/if}

				{#if turn.status === 'done' && finalText}
					<ButtonGroup>
						<Button color="input" on:click={handleCopy} size="small">
							{#snippet start()}
								<IconClipboard size={14} />
							{/snippet}
							Copy
						</Button>
						<Button color="input" on:click={addToEditor} size="small">
							{#snippet start()}
								<IconFileEarmark size={14} />
							{/snippet}
							Add to Editor
						</Button>
					</ButtonGroup>
				{/if}
			{/if}
		</div>
	</div>
</div>

<style lang="scss">
	.message-wrap {
		padding: 20px 25px;
		display: flex;
	}
	.message-wrap.ai {
		background-color: #fafafa;
	}
	.message {
		padding: 0 12px;
		min-width: 0;
		flex: 1;
	}
	.message-input {
		line-height: 28px;
	}
	.error {
		color: var(--red);
		font-weight: 600;
		font-size: 14px;
	}

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

	.message-html {
		line-height: 28px;

		:global(h1),
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5) {
			margin-top: 0;
			margin-bottom: 15px;
		}

		:global(h1) {
			font-size: 1.6rem;
		}

		:global(h2) {
			font-size: 1.4rem;
		}

		:global(h3) {
			font-size: 1.3rem;
		}
		:global(h4) {
			font-size: 1.2rem;
		}
		:global(h5) {
			font-size: 1.1rem;
		}
		:global(h6) {
			font-size: 1rem;
		}

		:global(p) {
			margin-top: 0;
		}

		:global(ul),
		:global(ol) {
			padding-left: 30px;
		}

		:global(li p) {
			margin: 0;
		}

		:global(a) {
			color: var(--link);
			text-decoration: underline;
		}
	}
</style>
