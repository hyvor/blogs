<script lang="ts">
	import { marked } from 'marked';
	// @ts-ignore
	import DOMPurify from 'dompurify';
	import { Button, IconMessage, Loader, Textarea } from '@hyvor/design/components';
	import IconRobot from '@hyvor/icons/IconRobot';
	import IconMagic from '@hyvor/icons/IconMagic';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheck from '@hyvor/icons/IconCheck';

	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import DiffReviewModal from './DiffReviewModal.svelte';
	import AgentSteps from './AgentSteps.svelte';
	import {
		applyAgentEvent,
		callAgent,
		DEFAULT_CONTENT_JSON,
		type AgentBlock,
		type AgentPostVariant,
		type DocumentChange
	} from './agentApi';

	interface Props {
		postVariantId: number | null;
		placeholder?: string;
		emptyMessage?: string;
		disclaimer?: string;
		applyDocumentChange: (change: DocumentChange, postVariant: AgentPostVariant) => Promise<void> | void;
	}

	let {
		postVariantId,
		placeholder = 'Type your prompt here...',
		emptyMessage = `Describe what you'd like the agent to do, e.g. "Fix any typos in the post" or "Add a short FAQ section at the end".`,
		disclaimer = 'The agent may suggest edits to this post.',
		applyDocumentChange
	}: Props = $props();

	let prompt = $state('');
	let status: 'idle' | 'streaming' | 'done' | 'error' = $state('idle');
	let error: string | null = $state(null);
	let sentPrompt = $state('');
	let postVariant: AgentPostVariant | null = $state(null);
	let blocks: AgentBlock[] = $state([]);
	let documentChange: DocumentChange | null = $state(null);
	let showDiffModal = $state(false);
	let applied = $state(false);
	let applying = $state(false);

	let finalText = $derived(
		blocks
			.filter((b) => b.type === 'text')
			.map((b) => b.content)
			.join('')
	);

	let responseHtml = $derived.by(() => {
		if (!finalText) return '';
		return DOMPurify.sanitize(marked(finalText) as string);
	});

	async function handleSubmit() {
		const userPrompt = prompt.trim();
		if (!userPrompt || status === 'streaming') return;

		sentPrompt = userPrompt;
		prompt = '';
		status = 'streaming';
		error = null;
		postVariant = null;
		blocks = [];
		documentChange = null;
		applied = false;

		try {
			await callAgent(userPrompt, postVariantId, (event) => {
				if (event.type === 'post_variant') {
					postVariant = event.post_variant;
				} else if (event.type === 'document_change') {
					documentChange = { postVariantId: event.post_variant_id, content: event.content };
				} else if (event.type !== 'done') {
					applyAgentEvent(blocks, event);
				}
			});
			status = 'done';
		} catch (err) {
			status = 'error';
			error = err instanceof Error ? err.message : 'Something went wrong. Please try again.';
		}
	}

	function reset() {
		sentPrompt = '';
		status = 'idle';
		error = null;
		postVariant = null;
		blocks = [];
		documentChange = null;
		applied = false;
	}

	async function handleFinishReview(finalContent: string) {
		if (!documentChange || !postVariant) return;
		applying = true;
		try {
			await applyDocumentChange({ ...documentChange, content: finalContent }, postVariant);
			applied = true;
			showDiffModal = false;
		} finally {
			applying = false;
		}
	}
</script>

<div class="agent-chat">
	<div class="body">
		<div class="agent-inner">
			{#if status === 'idle'}
				<IconMessage icon={IconRobot} message={emptyMessage} />
			{:else}
				<div class="turn">
					<div class="message-wrap user">
						<div class="avatar user-avatar"><span>You</span></div>
						<div class="message">{sentPrompt}</div>
					</div>

					<div class="message-wrap ai">
						<div class="avatar ai-avatar"><IconRobot size={16} /></div>
						<div class="message">
							{#if postVariant}
								<a
									class="post-pill"
									href={consoleUrlWithBlog(`/posts/${postVariant.post_id}`)}
									target="_blank"
								>
									<IconFileText size={12} />
									<span>{postVariant.title || 'Untitled post'}</span>
									<IconBoxArrowUpRight size={11} />
								</a>
							{/if}

							{#if blocks.length === 0 && !finalText}
								{#if status === 'error'}
									<span class="error">{error}</span>
								{:else}
									<Loader size="small" />
								{/if}
							{:else}
								<AgentSteps {blocks} {postVariant} />

								{#if finalText}
									<div class="message-html">
										{@html responseHtml}
									</div>
								{:else if status === 'streaming'}
									<Loader size="small" />
								{/if}

								{#if status === 'error'}
									<span class="error">{error}</span>
								{/if}
							{/if}

							{#if status === 'done' && documentChange}
								<div class="change-notice">
									{#if applied}
										<span class="applied-notice"><IconCheck size={13} /> Changes applied</span>
									{:else}
										<Button size="small" onclick={() => (showDiffModal = true)}>
											Review suggested changes
										</Button>
									{/if}
								</div>
							{/if}
						</div>
					</div>
				</div>

				{#if status === 'done' || status === 'error'}
					<div class="reset-button">
						<Button size="small" color="input" onclick={reset}>
							{#snippet start()}
								<IconArrowClockwise />
							{/snippet}
							New request
						</Button>
					</div>
				{/if}
			{/if}
		</div>
	</div>

	<div class="input-zone">
		<div class="agent-inner">
			<div class="input-row">
				<div class="prompt-input">
					<Textarea
						block={true}
						{placeholder}
						rows={1}
						bind:value={prompt}
						disabled={status === 'streaming'}
					/>
				</div>
				<Button disabled={prompt.trim() === '' || status === 'streaming'} onclick={handleSubmit}>
					<div class="generate-button-content">
						Send
						<div class="generate-icon"><IconMagic /></div>
					</div>
				</Button>
			</div>
			<div class="disclaimer">
				{disclaimer}
			</div>
		</div>
	</div>
</div>

{#if showDiffModal && documentChange && postVariant}
	<DiffReviewModal
		leftContent={postVariant.content_unsaved || postVariant.content || DEFAULT_CONTENT_JSON}
		rightContent={documentChange.content}
		{applying}
		onclose={() => (showDiffModal = false)}
		onfinish={handleFinishReview}
	/>
{/if}

<style lang="scss">
	.agent-chat {
		flex: 1;
		min-height: 0;
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}

	.agent-inner {
		width: 800px;
		max-width: 100%;
		margin: auto;
	}

	.body {
		flex: 1;
		overflow: auto;
	}

	.body .agent-inner {
		height: 100%;
	}

	.turn {
		display: flex;
		flex-direction: column;
	}

	.message-wrap {
		padding: 20px 30px;
		display: flex;
		gap: 12px;
	}

	.message-wrap.ai {
		background-color: #fafafa;
	}

	.avatar {
		flex-shrink: 0;
		width: 30px;
		height: 30px;
		border-radius: 50%;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.user-avatar {
		background-color: var(--accent-light-mid);
		font-size: 11px;
		font-weight: 600;
	}

	.ai-avatar {
		background-color: var(--accent-light-mid);
		color: var(--accent);
	}

	.message {
		min-width: 0;
		flex: 1;
	}

	.message-wrap.user .message {
		line-height: 28px;
	}

	.post-pill {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: 12px;
		font-weight: 600;
		padding: 4px 10px;
		border-radius: 20px;
		background-color: var(--box-background);
		border: 1px solid var(--border);
		color: inherit;
		text-decoration: none;
		margin-bottom: 12px;
	}

	.error {
		color: var(--red);
		font-weight: 600;
		font-size: 14px;
	}

	.change-notice {
		margin-top: 12px;
	}

	.applied-notice {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		font-size: 13px;
		font-weight: 600;
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

	.reset-button {
		padding: 0 30px 20px;
	}

	.input-zone {
		padding: 15px 30px 20px;
		border-top: 1px solid var(--border);
	}

	.input-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
		margin-bottom: 6px;
	}

	.prompt-input {
		width: 100%;
	}

	.generate-button-content {
		display: flex;
		font-size: 12px;
		align-items: center;
		white-space: nowrap;
	}

	.generate-icon {
		margin-left: 5px;
	}

	.disclaimer {
		font-size: 12px;
		color: var(--text-light);
	}
</style>
