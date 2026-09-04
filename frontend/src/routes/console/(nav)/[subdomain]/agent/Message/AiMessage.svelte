<script lang="ts">
	import type { AiMessage } from '../aiConversationApi';
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

		<DocumentChangeEvent events={documentChangeEvents} postVariants={[]} />
	</div>
</div>

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
