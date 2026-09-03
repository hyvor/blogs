<script lang="ts">
	import { getAiConversation, type AiConversation, type AiMessage } from './aiConversationApi';
	import { callAgent } from './agentApi';
	import Input from './Input.svelte';
	import dayjs from 'dayjs';
	import { onMount } from 'svelte';
	import { replaceState } from '$app/navigation';
	import IdleMessage from './IdleMessage.svelte';
	import { Loader, toast } from '@hyvor/design/components';
	import Messages from './Message/Messages.svelte';
	import { agentConversationsStore } from './agentConversationsStore';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';

	interface Props {
		conversationId: number | null;
	}

	let { conversationId = null }: Props = $props();

	let loading = $state(true);
	let conversation: null | AiConversation = $state(null);
	let messages: AiMessage[] = $state([]);
	let messagesComponent: Messages;
	let replying = $state(false);

	async function submit(prompt: string) {
		if (messagesComponent) {
			messagesComponent.setAutoScroll(false);
		}

		replying = true;

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
					agentConversationsStore.upsert(conversation.id, conversation.title);
					agentConversationsStore.setActive(conversation.id);
					replaceState(consoleUrlWithBlog(`/agent/${conversation.id}`), {});
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
				} else if (chunk.type === 'done') {
					replying = false;
				}
			});
		} catch (error) {
			console.error(error);
		} finally {
			replying = false;
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
				<Messages {messages} {replying} bind:this={messagesComponent} />
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
</style>
