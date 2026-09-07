<script lang="ts">
	import { getAiConversation } from './aiConversationApi';
	import { callAgent } from './agentApi';
	import Input from './Input.svelte';
	import dayjs from 'dayjs';
	import { onMount } from 'svelte';
	import IdleMessage from './IdleMessage.svelte';
	import { Loader, toast } from '@hyvor/design/components';
	import Messages from './Message/Messages.svelte';
	import { getI18n } from '../../../lib/i18n';
	import {
		draftSessionKey,
		deleteAgentChatSession,
		getAgentChatSession,
		setAgentChatSession,
		replyingSessionsStore,
		setSessionReplying,
		setPostVariantReplying
	} from './agentChatSessionStore';
	import type { AiConversation, AiMessage } from '../../../lib/types';

	interface Props {
		conversationUuid?: string | null;
		postVariantId?: number | null;
		onConversationCreated?: (conversation: AiConversation) => void;
	}

	let { conversationUuid = null, postVariantId = null, onConversationCreated }: Props = $props();

	const i18n = getI18n();

	// while a conversation hasn't been created yet (no uuid), it's cached under a draft key
	// scoped to this postVariantId/blog, so a fresh "new chat" doesn't inherit a finished one
	let sessionKey = $derived(conversationUuid ?? draftSessionKey(postVariantId));

	let loading = $state(true);
	let conversation: null | AiConversation = $state(null);
	let messages: AiMessage[] = $state([]);
	let messagesComponent: Messages;
	// sourced from a module-level store (not a local $state) so that a background stream
	// started by a since-unmounted AgentChat instance (e.g. the post editor's AI popover was
	// closed mid-reply) still shows as "replying" once this component remounts for it
	let replying = $derived($replyingSessionsStore.has(sessionKey));

	// guards the persistence effect below until the initial restore-from-cache/fetch in onMount
	// has run, otherwise it would fire with the blank initial state and wipe out a cached session
	// before it's read
	let restored = $state(false);

	// keeps the live conversation/messages cached under sessionKey, so this component can be
	// unmounted (e.g. the post editor's AI popover closing) and remounted later - or the active
	// conversation switched away and back - without losing an in-progress conversation
	$effect(() => {
		if (!restored) return;
		setAgentChatSession(sessionKey, { conversation, messages });
	});

	async function submit(prompt: string) {
		if (messagesComponent) {
			messagesComponent.setAutoScroll(false);
		}

		// tracks this in-flight submission's session key - it moves from the draft key to the
		// conversation's real uuid once conversation_created fires. Kept as a local variable
		// (not re-read from the sessionKey prop) since this function keeps running - and must
		// keep the "replying" flag and cache slot accurate - even after the component that
		// called it unmounts
		let replyingKey = sessionKey;
		setSessionReplying(replyingKey, true);
		setPostVariantReplying(postVariantId, true);

		// captured before the placeholder conversation below is assigned, so a first message
		// (no conversation yet) still sends null rather than the placeholder's fake id
		const existingConversationId = conversation?.id ?? null;

		if (conversation === null) {
			conversation = {
				id: -1,
				uuid: '',
				created_at: dayjs().unix(),
				updated_at: dayjs().unix(),
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
			await callAgent(prompt, postVariantId, existingConversationId, (chunk) => {
				const lastEvent = assistantMessage.events[assistantMessage.events.length - 1];

				if (chunk.type === 'conversation_created') {
					conversation = chunk.conversation;
					conversationUuid = conversation.uuid;

					// the draft slot is now represented by the real conversation's own session key
					deleteAgentChatSession(replyingKey);
					setSessionReplying(replyingKey, false);
					replyingKey = conversationUuid;
					setSessionReplying(replyingKey, true);

					onConversationCreated?.(conversation);
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
					setSessionReplying(replyingKey, false);
				}
			});
		} catch (error) {
			console.error(error);
		} finally {
			setSessionReplying(replyingKey, false);
			setPostVariantReplying(postVariantId, false);
		}
	}

	onMount(() => {
		const cached = getAgentChatSession(sessionKey);

		if (cached) {
			conversation = cached.conversation;
			messages = cached.messages;
			loading = false;
			restored = true;
		} else if (conversationUuid) {
			getAiConversation(conversationUuid)
				.then((res) => {
					conversation = res.conversation;
					messages = res.messages;
					loading = false;
				})
				.catch((e) => {
					toast.error(e.message || i18n.t('console.agent.loadConversationFailed'));
				})
				.finally(() => {
					restored = true;
				});
		} else {
			loading = false;
			restored = true;
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
