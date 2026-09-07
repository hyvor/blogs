<script lang="ts">
	import { onMount } from 'svelte';
	import UserMessage from './UserMessage.svelte';
	import AiMessageView from './AiMessage.svelte';
	import { Loader } from '@hyvor/design/components';
	import type { AiMessage } from '../../../../lib/types';

	interface Props {
		messages: AiMessage[];
		replying?: boolean;
	}

	let { messages, replying = false }: Props = $props();

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

	onMount(() => {
		if (!messagesEl) return;

		const el = messagesEl;

		// jump to the bottom once when the container first mounts (e.g. loading
		// an existing conversation), before the observer below can catch anything
		if (autoScroll) {
			scrollToBottom();
		}

		const observer = new MutationObserver(() => {
			if (autoScroll) {
				scrollToBottom();
			}
		});

		observer.observe(el, { childList: true, subtree: true, characterData: true });

		return () => observer.disconnect();
	});

	export function setAutoScroll(value: boolean) {
		autoScroll = value;
	}
</script>

<div class="messages" bind:this={messagesEl} onscroll={handleScroll}>
	<div class="messages-inner">
		{#each messages as message}
			{#if message.role === 'user'}
				<UserMessage content={message.content} />
			{:else}
				<AiMessageView {message} />
			{/if}
		{/each}

		{#if replying}
			<div class="replying-loader">
				<Loader size="small" colorTrack="transparent" />
			</div>
		{/if}
	</div>
</div>

<style>
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

	.replying-loader {
		padding: 0 30px 20px;
	}
</style>
