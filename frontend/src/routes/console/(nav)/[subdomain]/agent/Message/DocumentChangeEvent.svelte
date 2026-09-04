<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconClock from '@hyvor/icons/IconClock';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import type { AiConversationPostVariant, AiMessageEvent } from '../aiConversationApi';
	import PostStatusTag from '../../posts/PostStatusTag.svelte';
	import type { DocumentChange } from '../agentApi';
	import DiffReviewModal from '../Review/DiffReviewModal.svelte';

	interface Props {
		events: AiMessageEvent[];
		postVariants: AiConversationPostVariant[];
	}

	let { events, postVariants = [] }: Props = $props();

	function findPostVariant(postVariantId: number | null | undefined) {
		if (!postVariantId) return null;
		return postVariants.find((v) => v.id === postVariantId) ?? null;
	}

	let reviewChange: DocumentChange | null = $state(null);

	function openReview(event: AiMessageEvent) {
		reviewChange = {
			postVariantId: event.post_variant_id!,
			content: event.document_content!,
			version: event.post_variant_version!
		};
	}
</script>

{#if events.length > 0}
	<div class="doc-changes-panel">
		<div class="doc-changes-header">
			<IconFileText size={13} />
			<span>Document changes</span>
		</div>
		<div class="doc-changes-list">
			{#each events as event}
				{@const variant = findPostVariant(event.post_variant_id)}
				{@const reviewed = event.document_change_status === 'reviewed'}
				<div class="doc-change-row">
					<div class="doc-change-status" class:reviewed>
						{#if reviewed}
							<IconCheckCircleFill size={14} />
						{:else}
							<IconClock size={14} />
						{/if}
					</div>
					<div class="doc-change-info">
						<div class="doc-change-title">
							{variant?.title ?? `Post #${event.post_variant_id}`}
						</div>
						<div class="doc-change-meta">
							{#if variant}
								<PostStatusTag status={variant.status} size="x-small" />
							{/if}
							<span class="doc-change-status-label" class:reviewed>
								{reviewed ? 'Reviewed' : 'Needs review'}
							</span>
						</div>
					</div>
					<Button size="small" color="input" onclick={() => openReview(event)}
						>Review changes</Button
					>
				</div>
			{/each}
		</div>
	</div>
{/if}

{#if reviewChange}
	<DiffReviewModal change={reviewChange} onclose={() => (reviewChange = null)} />
{/if}

<style>
	.doc-changes-panel {
		margin-top: 16px;
		border: 1px solid var(--accent-light-mid);
		background-color: color-mix(in srgb, var(--accent), transparent 95%);
		border-radius: 20px;
		overflow: hidden;
	}

	.doc-changes-header {
		display: flex;
		align-items: center;
		gap: 6px;
		padding: 10px 20px;
		font-size: 12px;
		font-weight: 600;
		color: var(--accent);
		border-bottom: 1px solid var(--accent-light-mid);
	}

	.doc-changes-list {
		display: flex;
		flex-direction: column;
	}

	.doc-change-row {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 10px 20px;
	}
	.doc-change-row {
		border-top: 1px solid var(--accent-light-mid);
	}

	.doc-change-status {
		flex-shrink: 0;
		display: inline-flex;
		color: var(--orange);
	}

	.doc-change-status.reviewed {
		color: var(--green);
	}

	.doc-change-info {
		min-width: 0;
		flex: 1;
	}

	.doc-change-title {
		font-size: 13px;
		font-weight: 600;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.doc-change-meta {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-top: 3px;
	}

	.doc-change-status-label {
		font-size: 11px;
		color: var(--text-light);
	}

	.doc-change-status-label.reviewed {
		color: var(--green);
	}
</style>
