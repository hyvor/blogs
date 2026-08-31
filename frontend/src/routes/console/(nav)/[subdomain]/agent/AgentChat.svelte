<script lang="ts">
	import { marked } from 'marked';
	// @ts-ignore
	import DOMPurify from 'dompurify';
	import { IconButton, Loader, toast } from '@hyvor/design/components';
	import IconRobot from '@hyvor/icons/IconRobot';
	import IconArrowUpCircleFill from '@hyvor/icons/IconArrowUpCircleFill';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconCheck from '@hyvor/icons/IconCheck';

	import DiffReviewModal from './DiffReviewModal.svelte';
	import AgentSteps from './AgentSteps.svelte';
	import {
		applyAgentEvent,
		callAgent,
		getAgentConversation,
		type AgentBlock,
		type DocumentChange
	} from './agentApi';
	import IdleMessage from './IdleMessage.svelte';
	import { blogStore } from '../../../lib/stores/blogStore';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';

	interface UserTurn {
		role: 'user';
		content: string;
	}

	interface AssistantTurn {
		role: 'assistant';
		blocks: AgentBlock[];
		documentChanges: DocumentChange[];
		appliedIds: Set<number>;
		status: 'streaming' | 'done' | 'error';
		error: string | null;
	}

	type Turn = UserTurn | AssistantTurn;

	interface Props {
		postVariantId: number | null;
		placeholder?: string;
		emptyMessage?: string;
		applyDocumentChange: (change: DocumentChange) => Promise<void> | void;
		initialConversationId?: number | null;
		onConversationStarted?: (conversationId: number, title: string | null) => void;
	}

	let {
		postVariantId,
		placeholder = 'Type your prompt here...',
		applyDocumentChange,
		initialConversationId = null,
		onConversationStarted
	}: Props = $props();

	let prompt = $state('');
	let textareaEl: HTMLTextAreaElement | undefined = $state();
	let bodyEl: HTMLDivElement | undefined = $state();
	let turns: Turn[] = $state([]);
	let sending = $state(false);
	let loadingHistory = $state(false);
	let conversationId: number | null = $state(initialConversationId);
	let showDiffModal = $state(false);
	let reviewTurn: AssistantTurn | null = $state(null);
	let reviewPostVariantId: number | null = $state(null);
	let applying = $state(false);
	let scrollTick = $state(0);

	// guards the history-loading effect below against reloading a conversation this component
	// already knows about - either because it just finished loading it, or because it just
	// started it itself via a live prompt (see the 'conversation_started' handling below)
	let lastLoadedConversationId: number | null = null;

	function turnText(blocks: AgentBlock[]) {
		return blocks
			.filter((b) => b.type === 'text')
			.map((b) => b.content)
			.join('');
	}

	function turnHtml(blocks: AgentBlock[]) {
		const text = turnText(blocks);
		if (!text) return '';
		return DOMPurify.sanitize(marked(text) as string);
	}

	async function loadHistory(id: number) {
		loadingHistory = true;
		turns = [];

		try {
			const detail = await getAgentConversation(id);

			turns = detail.turns.map((turn): Turn => {
				if (turn.role === 'user') {
					return { role: 'user', content: turn.content };
				}

				const blocks: AgentBlock[] = [];
				let status: 'done' | 'error' = 'done';
				let error: string | null = null;

				for (const event of turn.events) {
					if (event.type === 'error') {
						status = 'error';
						error = event.message;
					} else {
						applyAgentEvent(blocks, event);
					}
				}

				return {
					role: 'assistant',
					blocks,
					documentChanges: [],
					appliedIds: new Set(),
					status,
					error
				};
			});
		} catch {
			toast.error('Failed to load this conversation.');
		} finally {
			loadingHistory = false;
		}
	}

	$effect(() => {
		const id = initialConversationId;
		if (id === lastLoadedConversationId) return;
		lastLoadedConversationId = id;
		conversationId = id;

		if (id === null) {
			turns = [];
		} else {
			loadHistory(id);
		}
	});

	function scrollToBottom() {
		if (bodyEl) {
			bodyEl.scrollTop = bodyEl.scrollHeight;
		}
	}

	$effect(() => {
		scrollTick;
		scrollToBottom();
	});

	async function handleSubmit() {
		const userPrompt = prompt.trim();
		if (!userPrompt || sending) return;

		prompt = '';
		sending = true;

		turns.push(
			{ role: 'user', content: userPrompt },
			{
				role: 'assistant',
				blocks: [],
				documentChanges: [],
				appliedIds: new Set(),
				status: 'streaming',
				error: null
			}
		);
		// re-read the just-pushed turn from the reactive $state array instead of keeping the
		// plain object literal above - Svelte only wraps values in its reactive proxy the
		// moment they're read back out of state, so mutating the literal directly (instead of
		// this reference) would silently skip reactivity and never update the UI
		const assistantTurn = turns[turns.length - 1] as AssistantTurn;
		scrollTick++;

		try {
			await callAgent(userPrompt, postVariantId, conversationId, (event) => {
				if (event.type === 'conversation_created') {
					const isNewConversation = conversationId === null;
					conversationId = event.conversation_id;
					lastLoadedConversationId = event.conversation_id;
					if (isNewConversation) {
						onConversationStarted?.(event.conversation_id, event.title);
					}
				} else if (event.type === 'error') {
					assistantTurn.status = 'error';
					assistantTurn.error = event.message;
				} else if (event.type === 'document_change') {
					const existingIndex = assistantTurn.documentChanges.findIndex(
						(c) => c.postVariantId === event.post_variant_id
					);
					const change = { postVariantId: event.post_variant_id, content: event.content };
					if (existingIndex >= 0) {
						assistantTurn.documentChanges[existingIndex] = change;
					} else {
						assistantTurn.documentChanges.push(change);
					}
				} else if (event.type !== 'done') {
					applyAgentEvent(assistantTurn.blocks, event);
				}

				scrollTick++;
			});

			if (assistantTurn.status !== 'error') {
				assistantTurn.status = 'done';

				const firstChange = assistantTurn.documentChanges[0];
				if (firstChange) {
					openReview(assistantTurn, firstChange.postVariantId);
				}
			}
		} catch (err) {
			assistantTurn.status = 'error';
			assistantTurn.error =
				err instanceof Error ? err.message : 'Something went wrong. Please try again.';
		} finally {
			sending = false;
		}
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			handleSubmit();
		}
	}

	function openReview(turn: AssistantTurn, postVariantId: number) {
		reviewTurn = turn;
		reviewPostVariantId = postVariantId;
		showDiffModal = true;
	}

	async function handleApplyChange(change: DocumentChange, finalContent: string) {
		if (!reviewTurn) return;
		const turn = reviewTurn;

		applying = true;
		try {
			await applyDocumentChange({ postVariantId: change.postVariantId, content: finalContent });
			turn.appliedIds.add(change.postVariantId);
			turn.appliedIds = new Set(turn.appliedIds);
		} finally {
			applying = false;
		}
	}

	function openAiSettings() {
		window.open(consoleUrlWithBlog('/settings/ai'), '_blank');
	}

	$effect(() => {
		prompt;
		if (textareaEl) {
			textareaEl.style.height = 'auto';
			textareaEl.style.height = Math.min(textareaEl.scrollHeight, 200) + 'px';
		}
	});
</script>

<div class="agent-chat">
	<div class="body" bind:this={bodyEl}>
		<div class="agent-inner">
			{#if loadingHistory}
				<div class="loading-history"><Loader /></div>
			{:else if turns.length === 0}
				<IdleMessage />
			{:else}
				{#each turns as turn}
					{#if turn.role === 'user'}
						<div class="message-wrap user">
							<div class="avatar user-avatar"><span>You</span></div>
							<div class="message">{turn.content}</div>
						</div>
					{:else}
						<div class="message-wrap ai">
							<div class="avatar ai-avatar"><IconRobot size={16} /></div>
							<div class="message">
								{#if turn.blocks.length === 0 && !turnText(turn.blocks)}
									{#if turn.status === 'error'}
										<span class="error">{turn.error}</span>
									{:else}
										<Loader size="small" />
									{/if}
								{:else}
									<AgentSteps blocks={turn.blocks} />

									{#if turnText(turn.blocks)}
										<div class="message-html">
											{@html turnHtml(turn.blocks)}
										</div>
									{:else if turn.status === 'streaming'}
										<Loader size="small" />
									{/if}

									{#if turn.status === 'error'}
										<span class="error">{turn.error}</span>
									{/if}
								{/if}

								{#if turn.documentChanges.length > 0}
									<div class="document-changes">
										{#each turn.documentChanges as change (change.postVariantId)}
											<button
												type="button"
												class="document-change-pill"
												class:applied={turn.appliedIds.has(change.postVariantId)}
												onclick={() => openReview(turn, change.postVariantId)}
											>
												<IconFileText size={12} />
												<span>Post #{change.postVariantId}</span>
												{#if turn.appliedIds.has(change.postVariantId)}
													<IconCheck size={12} />
												{/if}
											</button>
										{/each}
									</div>
								{/if}
							</div>
						</div>
					{/if}
				{/each}
			{/if}
		</div>
	</div>

	<div class="input-zone">
		<div class="agent-inner">
			<div class="input-row">
				<div class="prompt-input">
					<textarea
						bind:this={textareaEl}
						bind:value={prompt}
						onkeydown={handleKeydown}
						{placeholder}
						rows="1"
						disabled={sending}
					></textarea>
					<div class="send-button">
						<IconButton
							color="input"
							size="small"
							aria-label="Send"
							disabled={prompt.trim() === '' || sending}
							onclick={handleSubmit}
						>
							<IconArrowUpCircleFill size={20} />
						</IconButton>
					</div>
				</div>
			</div>
			<div class="footer-row">
				<div class="disclaimer">
					AI can make mistakes; please double-check. Conversations are deleted after 30 days.
				</div>
				{#if $blogStore?.ai_provider_model}
					<button type="button" class="model-info" onclick={openAiSettings}>
						{$blogStore.ai_provider_model}
					</button>
				{/if}
			</div>
		</div>
	</div>
</div>

{#if showDiffModal && reviewTurn}
	<DiffReviewModal
		changes={reviewTurn.documentChanges}
		initialPostVariantId={reviewPostVariantId}
		{applying}
		onclose={() => (showDiffModal = false)}
		onapply={handleApplyChange}
	/>
{/if}

<style lang="scss">
	.agent-chat {
		display: flex;
		flex-direction: column;
		overflow: hidden;
		height: 100%;
	}

	.agent-inner {
		width: 800px;
		max-width: 100%;
		margin: auto;
	}

	.body {
		flex: 1;
		overflow: auto;
		min-height: 0;
	}

	.body .agent-inner {
		height: 100%;
	}

	.loading-history {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
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
		white-space: pre-wrap;
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

	.input-zone {
		padding: 15px 30px 20px;
		border-top: 1px solid var(--border);
	}

	.input-row {
		margin-bottom: 6px;
	}

	.prompt-input {
		position: relative;
		width: 100%;
	}

	.prompt-input textarea {
		display: block;
		width: 100%;
		box-sizing: border-box;
		resize: none;
		border: none;
		outline: none;
		font-family: inherit;
		font-size: 14px;
		line-height: 1.4;
		color: inherit;
		background-color: var(--input);
		border-radius: 20px;
		border-bottom-right-radius: 0;
		padding: 12px 45px 12px 15px;
		max-height: 200px;
		overflow-y: auto;
		transition: 0.2s box-shadow;

		&:focus {
			box-shadow: 0 0 0 2px var(--accent-light);
		}

		&:disabled {
			opacity: 0.7;
		}
	}

	.send-button {
		position: absolute;
		right: 8px;
		bottom: 6px;
	}

	.footer-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}

	.disclaimer {
		font-size: 12px;
		color: var(--text-light);
	}

	.model-info {
		font-size: 12px;
		color: var(--text-light);
		background: none;
		border: none;
		padding: 0;
		cursor: pointer;
		flex-shrink: 0;

		&:hover {
			color: var(--text);
			text-decoration: underline;
		}
	}
</style>
