<script lang="ts">
	import type { DocumentChange } from '../agentApi';
	import type { AiMessage, AiMessageEvent } from '../aiConversationApi';
	import DiffReviewModal from '../Review/DiffReviewModal.svelte';
	import DocumentChangeEvent from './DocumentChangeEvent.svelte';
	import ErrorEvent from './ErrorEvent.svelte';
	import QueryEvent from './QueryEvent.svelte';
	import TextEvent from './TextEvent.svelte';
	import ThinkingEvent from './ThinkingEvent.svelte';

	interface Props {
		message: AiMessage;
	}

	let { message }: Props = $props();

	let documentChangeEvents = $derived(
		message.events.filter((event) => event.type === 'document_change')
	);

	let reviewChange: DocumentChange | null = $state(null);

	function openReview(event: AiMessageEvent) {
		reviewChange = {
			postVariantId: event.post_variant_id!,
			content: event.document_content!,
			version: event.post_variant_version!
		};
	}
</script>

<div class="message-wrap ai">
	<div class="message">
		{#each message.events as event}
			{#if event.type === 'thinking'}
				<ThinkingEvent {event} />
			{:else if event.type === 'query'}
				<QueryEvent {event} />
			{:else if event.type === 'text' && event.content}
				<TextEvent content={event.content} />
			{:else if event.type === 'error'}
				<ErrorEvent />
			{/if}
		{/each}

		<DocumentChangeEvent events={documentChangeEvents} postVariants={[]} onReview={openReview} />
	</div>
</div>

{#if reviewChange}
	<DiffReviewModal change={reviewChange} onclose={() => (reviewChange = null)} />
{/if}

<style>
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
