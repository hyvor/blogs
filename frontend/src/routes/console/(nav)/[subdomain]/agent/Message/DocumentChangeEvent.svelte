<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconFileText from '@hyvor/icons/IconFileText';
	import IconClock from '@hyvor/icons/IconClock';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import type { DocumentChange } from '../agentApi';
	import DiffReviewModal from '../Review/DiffReviewModal.svelte';
	import type { AiMessageEvent } from '../../../../lib/types';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import PostStatusText from '../../posts/PostStatusText.svelte';

	interface Props {
		events: AiMessageEvent[];
	}

	let { events }: Props = $props();

	let reviewChange: DocumentChange | null = $state(null);

	function openReview(event: AiMessageEvent) {
		if (!event.id) {
			console.error('Event does not have an ID');
			return;
		}

		reviewChange = {
			eventId: event.id!,
			postVariant: event.post_variant!,
			content: event.document_content!,
			version: event.post_variant_version!
		};
	}

	function handleApply(eventId: number) {
		events = events.map((event) =>
			event.id === eventId ? { ...event, document_change_status: 'reviewed' } : event
		);
		reviewChange = null;
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
				{@const variant = event.post_variant!}
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
							{variant.title || `Post variant #${variant.id}`}
						</div>
						<div class="doc-change-meta">
							<span class="doc-change-status-label" class:reviewed>
								{reviewed ? 'Reviewed' : 'Needs review'}
							</span>
							&middot;
							{#if variant}
								<PostStatusText status={variant.status} />
							{/if}
							{#if $languagesStore.length > 1}
								{@const language = $languagesStore.find((lang) => lang.id === variant.language_id)}
								{#if language}
									&middot;
									<span class="language-label">{language.name}</span>
								{/if}
							{/if}
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
	<DiffReviewModal
		change={reviewChange}
		onclose={() => (reviewChange = null)}
		onapply={handleApply}
	/>
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
		font-size: 11px;
	}

	.doc-change-status-label {
		color: var(--text-light);
	}

	.doc-change-status-label.reviewed {
		color: var(--green);
	}

	.language-label {
		color: var(--text-light);
	}
</style>
