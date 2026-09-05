<script lang="ts">
	import LicenseRequired from '../../../../../billing/LicenseRequired.svelte';
	import AgentChat from '../../../../agent/AgentChat.svelte';
	import { postVariantStore } from '../../../postStore';
	import { getAiConversations } from '../../../../agent/aiConversationApi';
	import { ActionList, ActionListItem, Button, Dropdown, Text } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import dayjs from 'dayjs';
	import {
		getRememberedConversationUuid,
		setRememberedConversationUuid
	} from '../../../../agent/agentChatSessionStore';
	import type { AiConversation } from '../../../../../../lib/types';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	let postVariantId = $derived($postVariantStore.id);

	let conversations = $state<AiConversation[]>([]);
	let showDropdown = $state(false);

	// null selectedUuid means the "new chat" draft for this post variant is showing.
	// draftNonce is bumped when "New Chat" is explicitly chosen, forcing AgentChat to remount
	// into a genuinely blank draft even if the previous draft already turned into a real
	// conversation (which is cached under its own uuid, not the draft key - see
	// agentChatSessionStore).
	let selectedUuid = $state<string | null>(null);
	let draftNonce = $state(0);
	// the conversation started from the current draft, if any - tracked only to highlight it in
	// the dropdown and show its title, without disturbing the live AgentChat instance
	let draftConversation = $state<AiConversation | null>(null);

	let chatKey = $derived(selectedUuid ?? `draft-${draftNonce}`);
	let activeUuid = $derived(selectedUuid ?? draftConversation?.uuid ?? null);
	let activeTitle = $derived(
		conversations.find((c) => c.uuid === activeUuid)?.title || draftConversation?.title
	);

	// restores whichever conversation (or "New Chat") was last active for this post variant -
	// either on this component's first mount (e.g. re-opening the AI popover) or when switching
	// to a different post variant (e.g. changing language) - and (re)loads that variant's
	// conversation history. The nonce is only bumped on an actual variant switch, not the first
	// run, so the initial mount doesn't immediately remount AgentChat a second time.
	let previousPostVariantId: number | undefined;
	$effect(() => {
		const id = postVariantId;

		if (previousPostVariantId === undefined) {
			selectedUuid = getRememberedConversationUuid(id) ?? null;
		} else if (previousPostVariantId !== id) {
			selectedUuid = getRememberedConversationUuid(id) ?? null;
			draftConversation = null;
			draftNonce++;
		}

		previousPostVariantId = id;
		loadConversations();
	});

	// remembers whichever conversation ends up active for this post variant, so re-opening the
	// AI popover later restores the same dropdown selection instead of resetting to "New Chat"
	$effect(() => {
		setRememberedConversationUuid(postVariantId, activeUuid);
	});

	async function loadConversations() {
		try {
			conversations = await getAiConversations(25, 0, postVariantId);
		} catch {
			conversations = [];
		}
	}

	function startNewChat() {
		showDropdown = false;
		if (selectedUuid === null && draftConversation === null) return;
		selectedUuid = null;
		draftConversation = null;
		draftNonce++;
	}

	function selectConversation(conversation: AiConversation) {
		showDropdown = false;
		selectedUuid = conversation.uuid;
	}

	function handleConversationCreated(conversation: AiConversation) {
		draftConversation = conversation;
		conversations = [conversation, ...conversations.filter((c) => c.uuid !== conversation.uuid)];
	}
</script>

<LicenseRequired licenseProperty="aiCost">
	{#snippet upgradeText()}
		<div>
			<T key="console.postEditor.agent.upgradeText" params={{ strong: { element: 'strong' } }} />
		</div>
	{/snippet}

	<div class="ai-panel">
		<div class="ai-header">
			<Dropdown bind:show={showDropdown} align="start" width={280}>
				{#snippet trigger()}
					<Button color="input" variant="invisible" size="small">
						<span class="conversation-title">{activeTitle || i18n.t('console.agent.newChat')}</span>

						{#snippet end()}
							<IconChevronDown size={10} />
						{/snippet}
					</Button>
				{/snippet}

				{#snippet content()}
					<ActionList>
						<ActionListItem on:select={startNewChat} disabled={activeUuid === null}>
							{#snippet start()}
								<IconPlus size={12} />
							{/snippet}
							{i18n.t('console.agent.newChat')}
						</ActionListItem>

						{#if conversations.length > 0}
							{#each conversations as conversation (conversation.uuid)}
								<ActionListItem
									on:select={() => selectConversation(conversation)}
									disabled={conversation.uuid === activeUuid}
									style={conversation.uuid === activeUuid
										? 'background-color: var(--accent-light-mid)'
										: ''}
								>
									<span class="conversation-item-title">
										{conversation.title || i18n.t('console.agent.untitledConversation')}
									</span>

									{#snippet description()}
										<Text small light>{dayjs.unix(conversation.updated_at).fromNow()}</Text>
									{/snippet}
								</ActionListItem>
							{/each}
						{/if}
					</ActionList>
				{/snippet}
			</Dropdown>
		</div>

		<div class="ai-body">
			{#key chatKey}
				<AgentChat
					{postVariantId}
					conversationUuid={selectedUuid}
					onConversationCreated={handleConversationCreated}
				/>
			{/key}
		</div>
	</div>
</LicenseRequired>

<style>
	.ai-panel {
		height: 100%;
		display: flex;
		flex-direction: column;
		min-height: 0;
	}

	.ai-header {
		flex-shrink: 0;
		padding: 10px 15px;
		border-bottom: 1px solid var(--border);
	}

	.conversation-title,
	.conversation-item-title {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		max-width: 180px;
		display: inline-block;
		vertical-align: middle;
	}

	.ai-body {
		flex: 1;
		min-height: 0;
	}
</style>
