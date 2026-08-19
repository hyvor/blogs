<script lang="ts">
	import IconRobot from '@hyvor/icons/IconRobot';
	import AgentChat from './AgentChat.svelte';
	import { saveAgentDocumentChange, type AgentPostVariant, type DocumentChange } from './agentApi';

	async function applyDocumentChange(change: DocumentChange, postVariant: AgentPostVariant) {
		await saveAgentDocumentChange(postVariant.post_id, postVariant.language_id, change.content);
	}
</script>

<div class="agent-page hds-box">
	<div class="header">
		<div class="agent-inner">
			<div class="title">
				<IconRobot size={18} />
				<span>AI Agent</span>
			</div>
			<div class="subtitle">
				Ask the agent to answer questions about your blog or edit one of your published posts.
			</div>
		</div>
	</div>

	<AgentChat
		postVariantId={null}
		disclaimer="The agent picks one published post from your blog and may suggest edits to it."
		{applyDocumentChange}
	/>
</div>

<style lang="scss">
	.agent-page {
		height: 100%;
		display: flex;
		flex-direction: column;
		overflow: hidden;
	}

	.agent-inner {
		width: 800px;
		max-width: 100%;
		margin: auto;
	}

	.header {
		padding: 20px 30px;
		border-bottom: 1px solid var(--border);
	}

	.title {
		display: flex;
		align-items: center;
		gap: 8px;
		font-weight: 600;
		font-size: 16px;
	}

	.subtitle {
		margin-top: 4px;
		font-size: 13px;
		color: var(--text-light);
	}
</style>
