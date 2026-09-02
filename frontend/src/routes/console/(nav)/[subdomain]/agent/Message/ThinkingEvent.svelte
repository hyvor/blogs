<script lang="ts">
	import IconLightbulb from '@hyvor/icons/IconLightbulb';
	import IconChevronDown from '@hyvor/icons/IconChevronDown';
	import type { AiMessageEvent } from '../aiConversationApi';

	interface Props {
		event: AiMessageEvent;
	}

	let { event }: Props = $props();

	let open = $state(false);
</script>

<div class="step-block">
	<button type="button" class="step-header" onclick={() => (open = !open)}>
		<IconLightbulb size={11} />
		<span>Thought process</span>
		<span class="chevron" class:open>
			<IconChevronDown size={10} />
		</span>
	</button>
	{#if open}
		<div class="step-content">
			<p>{event.content}</p>
		</div>
	{/if}
</div>

<style>
	.step-block {
		margin-bottom: 10px;
	}

	.step-header {
		display: flex;
		align-items: center;
		gap: 6px;
		background: none;
		border: none;
		padding: 0;
		margin: 0;
		font: inherit;
		font-size: 12px;
		color: var(--text-light);
		cursor: pointer;
		text-transform: capitalize;
	}

	.chevron {
		display: inline-flex;
		transition: transform 0.15s ease;
	}

	.chevron.open {
		transform: rotate(180deg);
	}

	.step-content {
		margin-top: 4px;
		padding: 2px 10px;
		border-left: 2px solid var(--border);
		font-size: 12px;
		line-height: 1.5;
		color: var(--text-light);
	}

	.step-content p {
		margin: 6px 0;
	}
</style>
