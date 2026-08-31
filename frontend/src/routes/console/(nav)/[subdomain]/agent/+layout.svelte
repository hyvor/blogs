<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/state';
	import { goto } from '$app/navigation';
	import { IconButton, Loader, confirm, toast } from '@hyvor/design/components';
	import IconPlus from '@hyvor/icons/IconPlus';
	import IconTrash from '@hyvor/icons/IconTrash';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { agentConversationsStore } from './agentConversationsStore';

	interface Props {
		children?: import('svelte').Snippet;
	}

	let { children }: Props = $props();

	let activeConversationId = $derived(
		page.params.conversationId ? Number(page.params.conversationId) : null
	);

	onMount(() => {
		agentConversationsStore.load();
	});

	function handleScroll(e: Event) {
		const el = e.currentTarget as HTMLElement;
		if (el.scrollTop + el.clientHeight >= el.scrollHeight - 100) {
			agentConversationsStore.loadMore();
		}
	}

	async function handleDelete(e: MouseEvent, id: number) {
		e.preventDefault();
		e.stopPropagation();

		const confirmed = await confirm({
			title: 'Delete Conversation',
			content: 'Are you sure you want to delete this conversation? This action is IRREVERSIBLE.',
			confirmText: 'Yes, Delete',
			danger: true,
			autoClose: false
		});

		if (!confirmed) return;

		confirmed.loading();

		try {
			await agentConversationsStore.remove(id);
			confirmed.close();

			if (activeConversationId === id) {
				goto(consoleUrlWithBlog('/agent'));
			}
		} catch {
			confirmed.close();
			toast.error('Failed to delete conversation');
		}
	}
</script>

<div class="agent-layout hds-box">
	<div class="nav">
		<a
			href={consoleUrlWithBlog('/agent')}
			class="conversation-link"
			class:active={!activeConversationId}
		>
			<div class="new-chat-button">
				<IconPlus size={14} />
				New Chat
			</div>
		</a>

		<div class="conversations" onscroll={handleScroll}>
			{#if $agentConversationsStore.loading}
				<div class="state-row"><Loader size="small" /></div>
			{:else if $agentConversationsStore.conversations.length === 0}
				<div class="state-row empty">No conversations yet</div>
			{:else}
				{#each $agentConversationsStore.conversations as conversation (conversation.id)}
					<a
						class="conversation-link"
						href={consoleUrlWithBlog(`/agent/${conversation.id}`)}
						class:active={activeConversationId === conversation.id}
					>
						<span class="conversation-title">{conversation.title || 'Untitled conversation'}</span>

						<div class="delete-button">
							<IconButton
								size="small"
								color="input"
								aria-label="Delete conversation"
								onclick={(e: MouseEvent) => handleDelete(e, conversation.id)}
							>
								<IconTrash size={12} />
							</IconButton>
						</div>
					</a>
				{/each}

				{#if $agentConversationsStore.loadingMore}
					<div class="state-row"><Loader size="small" /></div>
				{/if}
			{/if}
		</div>
	</div>

	<div class="content">
		{@render children?.()}
	</div>
</div>

<style>
	.agent-layout {
		display: grid;
		grid-template-columns: minmax(200px, 400px) 1fr;
		gap: 15px;
		height: 100%;
	}

	.nav {
		display: flex;
		flex-direction: column;
		height: 100%;
		overflow: hidden;
		min-width: 0;
		border-right: 1px solid var(--border);
		padding: 15px 0;
	}

	.conversations {
		flex: 1;
		overflow-y: auto;
		padding: 10px 0;
	}

	.conversation-title {
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		display: block;
	}

	.state-row {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px;
	}

	.state-row.empty {
		color: var(--text-light);
		font-size: 13px;
	}

	.conversation-link {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 10px 25px;
		text-decoration: none;
		color: var(--text);
		font-size: 14px;
		transition: 0.1s background-color;
		gap: 5px;
	}

	.conversation-link:hover {
		background-color: var(--hover);
	}

	.conversation-link.active {
		background-color: var(--accent-light-mid);
	}

	.new-chat-button {
		display: flex;
		align-items: center;
		gap: 5px;
	}

	.content {
		flex: 1;
		min-width: 0;
		height: 100%;
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}

	@media (max-width: 992px) {
		.agent-layout {
			grid-template-columns: 1fr;
			grid-template-rows: auto 1fr;
		}
		.nav {
			height: auto;
			max-height: 300px;
		}
	}
</style>
