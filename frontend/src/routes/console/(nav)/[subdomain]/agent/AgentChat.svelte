<script lang="ts">
	import {
		getAiConversation,
		type AiConversation,
		type AiMessage,
		type AiMessageEvent
	} from './aiConversationApi';
	import { applyDocumentChange, callAgent, type DocumentChange } from './agentApi';
	import UserMessage from './Message/UserMessage.svelte';
	import ThinkingEvent from './Message/ThinkingEvent.svelte';
	import QueryEvent from './Message/QueryEvent.svelte';
	import TextEvent from './Message/TextEvent.svelte';
	import DocumentChangeEvent from './Message/DocumentChangeEvent.svelte';
	import DiffReviewModal from './DiffReviewModal.svelte';
	import Input from './Input.svelte';
	import dayjs from 'dayjs';
	import { onMount } from 'svelte';
	import IdleMessage from './IdleMessage.svelte';
	import { Loader, toast } from '@hyvor/design/components';

	interface Props {
		conversationId: number | null;
	}

	let { conversationId = null }: Props = $props();

	function documentChangeEvents(message: AiMessage) {
		return message.events.filter((e) => e.type === 'document_change');
	}

	let loading = $state(true);
	let conversation: null | AiConversation = $state(null);
	let messages: AiMessage[] = $state([]);

	let showDiffModal = $state(false);
	let reviewChange: DocumentChange | null = $state(null);
	let applying = $state(false);

	function openReview(event: AiMessageEvent) {
		console.log(event);
		reviewChange = {
			postVariantId: event.post_variant_id!,
			content: event.document_content!,
			version: event.post_variant_version!
		};
		showDiffModal = true;
	}

	async function handleApply(
		change: DocumentChange,
		finalContent: string,
		documentVersion: number
	) {
		applying = true;
		try {
			await applyDocumentChange(change.postVariantId, finalContent, documentVersion);
		} catch (error) {
			toast.error(error instanceof Error ? error.message : 'Could not apply the change');
			throw error;
		} finally {
			applying = false;
		}
	}

	async function submit(prompt: string) {
		if (conversation === null) {
			conversation = {
				id: -1,
				created_at: dayjs().unix(),
				title: prompt.slice(0, 50)
			};
		}

		messages.push(
			{
				id: -1,
				created_at: dayjs().unix(),
				role: 'user',
				content: prompt,
				events: []
			},
			{
				id: -2,
				created_at: dayjs().unix(),
				role: 'assistant',
				content: '',
				events: []
			}
		);

		const assistantMessage = messages[messages.length - 1] as AiMessage;

		try {
			await callAgent(prompt, null, conversationId, (chunk) => {
				const lastEvent = assistantMessage.events[assistantMessage.events.length - 1];

				if (chunk.type === 'conversation_created') {
					conversation = chunk.conversation;
					conversationId = conversation.id;
				} else if (chunk.type === 'text_chunk') {
					if (lastEvent && lastEvent.type === 'text') {
						lastEvent.content += chunk.content;
					} else {
						assistantMessage.events.push({
							type: 'text',
							content: chunk.content
						});
					}
				} else if (chunk.type === 'thinking_chunk') {
					if (lastEvent && lastEvent.type === 'thinking') {
						lastEvent.content += chunk.content;
					} else {
						assistantMessage.events.push({
							type: 'thinking',
							content: chunk.content
						});
					}
				} else if (chunk.type === 'event') {
					assistantMessage.events.push(chunk.event);
				}
			});
		} catch (error) {
			console.error(error);
		}
	}

	onMount(() => {
		if (conversationId) {
			getAiConversation(conversationId)
				.then((res) => {
					conversation = res.conversation;
					messages = res.messages;
					loading = false;
				})
				.catch((e) => {
					toast.error(e.message || 'unable to load the conversation');
				});
		} else {
			loading = false;
		}
	});
</script>

{#if loading}
	<Loader full />
{:else}
	<div class="conversation-view">
		<div class="conversation-inner">
			{#if conversation}
				<div class="messages">
					{#each messages as message (message.id)}
						{#if message.role === 'user'}
							<UserMessage content={message.content} />
						{:else}
							<div class="message-wrap ai">
								<div class="message">
									{#each message.events as event}
										{#if event.type === 'thinking'}
											<ThinkingEvent {event} />
										{:else if event.type === 'query'}
											<QueryEvent {event} />
										{:else if event.type === 'text' && event.content}
											<TextEvent content={event.content} />
										{/if}
									{/each}

									<DocumentChangeEvent
										events={documentChangeEvents(message)}
										postVariants={[]}
										onReview={openReview}
									/>
								</div>
							</div>
						{/if}
					{/each}
				</div>
			{:else}
				<IdleMessage />
			{/if}
			<Input onsubmit={submit} />
		</div>
	</div>
{/if}

{#if showDiffModal && reviewChange}
	<DiffReviewModal
		change={reviewChange}
		{applying}
		onclose={() => (showDiffModal = false)}
		onapply={handleApply}
	/>
{/if}

<style>
	.conversation-view {
		height: 100%;
		overflow: auto;
	}

	.conversation-inner {
		width: 800px;
		max-width: 100%;
		margin: auto;
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.messages {
		flex: 1;
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
</style>
