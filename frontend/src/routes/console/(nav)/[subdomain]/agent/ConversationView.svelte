<script lang="ts">
	import { marked } from 'marked';
	// @ts-ignore
	import DOMPurify from 'dompurify';
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import type { AiConversation, AiMessage } from './aiConversationApi';
	import UserMessage from './Message/UserMessage.svelte';

	interface Props {
		conversation: AiConversation;
		messages: AiMessage[];
	}

	let { conversation, messages }: Props = $props();

	// tracks user-toggled thinking sections, keyed by message id
	let openThinking: Record<number, boolean> = $state({});

	function isThinkingOpen(messageId: number) {
		return openThinking[messageId] ?? false;
	}

	function toggleThinking(messageId: number) {
		openThinking[messageId] = !isThinkingOpen(messageId);
	}

	function contentHtml(content: string) {
		if (!content) return '';
		return DOMPurify.sanitize(marked(content) as string);
	}
</script>

<div class="conversation-view">
	<div class="conversation-inner">
		{#each messages as message (message.id)}
			{#if message.role === 'user'}
				<UserMessage content={message.content} />
			{:else}
				<div class="message-wrap ai">
					<div class="message">
						{#if message.thinking.length > 0}
							<div class="thinking-block">
								<button
									type="button"
									class="thinking-header"
									onclick={() => toggleThinking(message.id)}
								>
									<IconLightbulb size={13} />
									<span>Thought process</span>
									<span class="chevron" class:open={isThinkingOpen(message.id)}>
										<IconChevronDown size={12} />
									</span>
								</button>
								{#if isThinkingOpen(message.id)}
									<div class="thinking-content">
										{#each message.thinking as thought (thought.id)}
											<p>{thought.summary}</p>
										{/each}
									</div>
								{/if}
							</div>
						{/if}

						{#if message.content}
							<div class="message-html">
								{@html contentHtml(message.content)}
							</div>
						{/if}
					</div>
				</div>
			{/if}
		{/each}
	</div>
</div>

<style>
	.conversation-view {
		height: 100%;
		overflow: auto;
	}

	.conversation-inner {
		width: 800px;
		max-width: 100%;
		margin: auto;
		padding-bottom: 40px;
	}

	.message-wrap {
		padding: 20px 30px;
		display: flex;
		gap: 12px;
	}

	.message {
		min-width: 0;
		flex: 1;
	}

	.thinking-block {
		margin-bottom: 10px;
	}

	.thinking-header {
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
	}

	.chevron {
		display: inline-flex;
		transition: transform 0.15s ease;
	}

	.chevron.open {
		transform: rotate(180deg);
	}

	.thinking-content {
		margin-top: 4px;
		padding: 2px 10px;
		border-left: 2px solid var(--border);
		font-size: 12px;
		line-height: 1.5;
		color: var(--text-light);
	}

	.thinking-content p {
		margin: 6px 0;
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

		:global(p) {
			margin-top: 0;
		}

		:global(ul),
		:global(ol) {
			padding-left: 30px;
		}

		:global(a) {
			color: var(--link);
			text-decoration: underline;
		}
	}
</style>
