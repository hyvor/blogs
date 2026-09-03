<script lang="ts">
	import { IconButton } from '@hyvor/design/components';
	import { blogStore } from '../../../lib/stores/blogStore';
	import IconArrowUpCircleFill from '@hyvor/icons/IconArrowUpCircleFill';
	import { consoleUrlWithBlog } from '../../../lib/consoleUrl';
	import { onMount } from 'svelte';

	interface Props {
		onsubmit: (prompt: string) => void;
	}

	let { onsubmit }: Props = $props();

	let prompt = $state('');
	let sending = false;
	let textareaEl: HTMLTextAreaElement;

	function handleSubmit() {
		onsubmit(prompt);
		prompt = '';
	}

	function handleKeydown(e: KeyboardEvent) {
		if (e.key === 'Enter' && !e.shiftKey) {
			e.preventDefault();
			handleSubmit();
		}
	}

	function handleInput() {
		if (textareaEl) {
			textareaEl.style.height = 'auto';
			textareaEl.style.height = Math.min(textareaEl.scrollHeight, 200) + 'px';
		}
	}

	onMount(() => {
		textareaEl?.focus();
	});
</script>

<div class="input-zone">
	<div class="input-inner">
		<div class="input-row">
			<div class="prompt-input">
				<textarea
					bind:this={textareaEl}
					bind:value={prompt}
					onkeydown={handleKeydown}
					oninput={handleInput}
					placeholder="Type your prompt here"
					rows="1"
					disabled={sending}
				></textarea>
				<div class="send-button">
					<IconButton
						color="input"
						size="small"
						aria-label="Send"
						disabled={prompt.trim() === '' || sending}
						onclick={handleSubmit}
					>
						<IconArrowUpCircleFill size={20} />
					</IconButton>
				</div>
			</div>
		</div>
		<div class="footer-row">
			<div class="disclaimer">AI can make mistakes; please double-check.</div>
			{#if $blogStore?.ai_model}
				<a
					type="button"
					class="model-info"
					href={consoleUrlWithBlog('/settings/ai')}
					target="_blank"
				>
					{$blogStore.ai_model}
				</a>
			{/if}
		</div>
	</div>
</div>

<style>
	.input-zone {
		padding: 15px 30px 20px;
		border-top: 1px solid var(--border);
	}

	.input-inner {
		width: var(--ai-max-width);
		max-width: 100%;
		margin: auto;
	}

	.input-row {
		margin-bottom: 6px;
	}

	.prompt-input {
		position: relative;
		width: 100%;
	}

	textarea {
		display: block;
		width: 100%;
		box-sizing: border-box;
		resize: none;
		border: none;
		outline: none;
		font-family: inherit;
		font-size: 14px;
		line-height: 1.4;
		color: inherit;
		background-color: var(--input);
		border-radius: 20px;
		border-bottom-right-radius: 0;
		padding: 12px 45px 12px 15px;
		max-height: 200px;
		overflow-y: auto;
		transition: 0.2s box-shadow;
	}
	textarea:focus {
		box-shadow: 0 0 0 2px var(--accent-light);
	}

	textarea:disabled {
		opacity: 0.7;
	}

	.send-button {
		position: absolute;
		right: 8px;
		bottom: 6px;
	}

	.footer-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}

	.disclaimer {
		font-size: 12px;
		color: var(--text-light);
	}

	.model-info {
		font-size: 12px;
		color: var(--text-light);
		background: none;
		border: none;
		padding: 0;
		cursor: pointer;
		flex-shrink: 0;
	}
	.model-info:hover {
		color: var(--text);
		text-decoration: underline;
	}
</style>
