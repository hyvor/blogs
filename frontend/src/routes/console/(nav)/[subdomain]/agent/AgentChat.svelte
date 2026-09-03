<script lang="ts">
	import { getAiConversation, type AiConversation, type AiMessage } from './aiConversationApi';
	import { callAgent } from './agentApi';
	import UserMessage from './Message/UserMessage.svelte';
	import Input from './Input.svelte';
	import dayjs from 'dayjs';
	import { onMount } from 'svelte';
	import IdleMessage from './IdleMessage.svelte';
	import { Loader, toast } from '@hyvor/design/components';
	import AiMessageView from './Message/AiMessage.svelte';

	interface Props {
		conversationId: number | null;
	}

	let { conversationId = null }: Props = $props();

	let loading = $state(true);
	let conversation: null | AiConversation = $state(null);
	let messages: AiMessage[] = $state([]);

	let messagesEl: HTMLDivElement | undefined = $state();

	// whether we should keep scrolling to the bottom as new content streams in.
	// turned off when the user scrolls away from the bottom themselves.
	let autoScroll = $state(true);

	const SCROLL_BOTTOM_THRESHOLD = 60;

	function isNearBottom() {
		if (!messagesEl) return true;
		return (
			messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight <
			SCROLL_BOTTOM_THRESHOLD
		);
	}

	function handleScroll() {
		autoScroll = isNearBottom();
	}

	function scrollToBottom() {
		if (messagesEl) {
			messagesEl.scrollTop = messagesEl.scrollHeight;
		}
	}

	$effect(() => {
		if (!messagesEl) return;

		const el = messagesEl;

		// jump to the bottom once when the container first mounts (e.g. loading
		// an existing conversation), before the observer below can catch anything
		if (autoScroll) {
			scrollToBottom();
		}

		// content (text/thinking chunks) mutates nested state deeply while streaming,
		// which is cheaper to catch via DOM mutations than by deep-tracking every event
		const observer = new MutationObserver(() => {
			if (autoScroll) {
				scrollToBottom();
			}
		});

		observer.observe(el, { childList: true, subtree: true, characterData: true });

		return () => observer.disconnect();
	});

	async function submit(prompt: string) {
		autoScroll = true;

		if (conversation === null) {
			conversation = {
				id: -1,
				created_at: dayjs().unix(),
				title: prompt.slice(0, 50)
			};
		}

		messages.push(
			{
				created_at: dayjs().unix(),
				role: 'user',
				content: prompt,
				events: []
			},
			{
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
				<div class="messages" bind:this={messagesEl} onscroll={handleScroll}>
					<div class="messages-inner">
						{#each messages as message}
							{#if message.role === 'user'}
								<UserMessage content={message.content} />
							{:else}
								<AiMessageView {message} />
							{/if}
						{/each}
					</div>
				</div>
			{:else}
				<IdleMessage />
			{/if}
			<Input onsubmit={submit} />
		</div>
	</div>
{/if}

<style>
	.conversation-view {
		height: 100%;
		overflow: auto;
		--ai-max-width: 800px;
	}

	.conversation-inner {
		margin: auto;
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.messages {
		flex: 1;
		min-height: 0;
		overflow: auto;
	}

	.messages-inner {
		width: var(--ai-max-width);
		max-width: 100%;
		margin: auto;
	}
</style>
