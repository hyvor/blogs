<script lang="ts">
	import { marked } from 'marked';
	// @ts-ignore
	import DOMPurify from 'dompurify';
	import { Button, Loader, Textarea } from '@hyvor/design/components';
	import IconRobot from '@hyvor/icons/IconRobot';
	import IconMagic from '@hyvor/icons/IconMagic';
	import IconArrowClockwise from '@hyvor/icons/IconArrowClockwise';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconCheck from '@hyvor/icons/IconCheck';

	import DiffReviewModal from './DiffReviewModal.svelte';
	import AgentSteps from './AgentSteps.svelte';
	import { applyAgentEvent, callAgent, type AgentBlock, type DocumentChange } from './agentApi';
	import IdleMessage from './IdleMessage.svelte';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		postVariantId: number | null;
		placeholder?: string;
		emptyMessage?: string;
		applyDocumentChange: (change: DocumentChange) => Promise<void> | void;
	}

	let {
		postVariantId,
		placeholder = 'Type your prompt here...',
		applyDocumentChange
	}: Props = $props();

	let prompt = $state('');
	let status: 'idle' | 'streaming' | 'done' | 'error' = $state('idle');
	let error: string | null = $state(null);
	let sentPrompt = $state('');
	let blocks: AgentBlock[] = $state([]);
	let documentChanges: DocumentChange[] = $state([]);
	let appliedIds: Set<number> = $state(new Set());
	let showDiffModal = $state(false);
	let reviewPostVariantId: number | null = $state(null);
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
		blocks = [];
		documentChanges = [];
		appliedIds = new Set();
		showDiffModal = false;

		try {
			await callAgent(userPrompt, postVariantId, (event) => {
				if (event.type === 'document_change') {
					const existingIndex = documentChanges.findIndex(
						(c) => c.postVariantId === event.post_variant_id
					);
					const change = { postVariantId: event.post_variant_id, content: event.content };
					if (existingIndex >= 0) {
						documentChanges[existingIndex] = change;
					} else {
						documentChanges.push(change);
					}
				} else if (event.type !== 'done') {
					applyAgentEvent(blocks, event);
				}
			});
			status = 'done';

			const firstChange = documentChanges[0];
			if (firstChange) {
				openReview(firstChange.postVariantId);
			}
		} catch (err) {
			status = 'error';
			error = err instanceof Error ? err.message : 'Something went wrong. Please try again.';
		}
	}

	function reset() {
		sentPrompt = '';
		status = 'idle';
		error = null;
		blocks = [];
		documentChanges = [];
		appliedIds = new Set();
		showDiffModal = false;
	}

	function openReview(postVariantId: number) {
		reviewPostVariantId = postVariantId;
		showDiffModal = true;
	}

	async function handleApplyChange(change: DocumentChange, finalContent: string) {
		applying = true;
		try {
			await applyDocumentChange({ postVariantId: change.postVariantId, content: finalContent });
			appliedIds.add(change.postVariantId);
			appliedIds = new Set(appliedIds);
		} finally {
			applying = false;
		}
	}
</script>

<div class="agent-chat">
	<div class="body">
		<div class="agent-inner">
			{#if status === 'idle'}
				<IdleMessage />
			{:else}
				<div class="turn">
					<div class="message-wrap user">
						<div class="avatar user-avatar"><span>You</span></div>
						<div class="message">{sentPrompt}</div>
					</div>

					<div class="message-wrap ai">
						<div class="avatar ai-avatar"><IconRobot size={16} /></div>
						<div class="message">
							{#if blocks.length === 0 && !finalText}
								{#if status === 'error'}
									<span class="error">{error}</span>
								{:else}
									<Loader size="small" />
								{/if}
							{:else}
								<AgentSteps {blocks} />

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

							{#if documentChanges.length > 0}
								<div class="document-changes">
									{#each documentChanges as change (change.postVariantId)}
										<button
											type="button"
											class="document-change-pill"
											class:applied={appliedIds.has(change.postVariantId)}
											onclick={() => openReview(change.postVariantId)}
										>
											<IconFileText size={12} />
											<span>Post #{change.postVariantId}</span>
											{#if appliedIds.has(change.postVariantId)}
												<IconCheck size={12} />
											{/if}
										</button>
									{/each}
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
							{i18n.t('console.agent.newRequest')}
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
						{i18n.t('console.agent.send')}
						<div class="generate-icon"><IconMagic /></div>
					</div>
				</Button>
			</div>
			<div class="disclaimer">AI can make mistakes; please double-check.</div>
		</div>
	</div>
</div>

{#if showDiffModal && documentChanges.length > 0}
	<DiffReviewModal
		changes={documentChanges}
		initialPostVariantId={reviewPostVariantId}
		{applying}
		onclose={() => (showDiffModal = false)}
		onapply={handleApplyChange}
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

	.error {
		color: var(--red);
		font-weight: 600;
		font-size: 14px;
	}

	.document-changes {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 12px;
	}

	.document-change-pill {
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
		cursor: pointer;
	}

	.document-change-pill.applied {
		color: var(--green);
		border-color: var(--green-light);
		background-color: var(--green-light);
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
