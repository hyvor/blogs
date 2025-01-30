<script lang="ts">
	import { Button, ButtonGroup, Loader, toast } from '@hyvor/design/components';
	import UserLogo from './UserLogo.svelte';
	import { appendHtml, copyHtmlToClipboard, getHtmlFromMarkdownResponse } from './ai';
	import IconClipboard from '@hyvor/icons/IconClipboard';
	import IconFileEarmark from '@hyvor/icons/IconFileEarmark';

	import { blogStore } from '../../../../../../lib/stores/blogStore';
	import logo from '$lib/img/logo.png';
	import { postEditingStatusStore } from '../../../postStore';

	interface Props {
		prompt: string;
		response?: null | string;
		error?: null | string | boolean;
	}

	let { prompt, response = null, error = null }: Props = $props();

	let htmlEl: HTMLDivElement | undefined = $state();

	let responseHtml = $derived(getHtmlFromMarkdownResponse(response));

	function handleCopy() {
		if (!htmlEl) return;
		copyHtmlToClipboard(htmlEl);
		toast.success('Copied to clipboard');
	}

	function addToEditor() {
		if (!htmlEl) return;
		appendHtml($postEditingStatusStore.editorView!, htmlEl.innerHTML);
	}
</script>

<div class="single">
	<div class="message-wrap">
		<UserLogo url={$blogStore.icon_url || $blogStore.logo_url} />
		<div class="message message-input">{prompt}</div>
	</div>

	<div class="message-wrap ai">
		<UserLogo url={logo} ai />
		<div class="message">
			{#if response === null}
				{#if error === null}
					<Loader size="small" />
				{:else}
					<span class="error"
						>{typeof error === 'string'
							? error
							: 'Something went wrong. Please try again.'}</span
					>
				{/if}
			{:else}
				<div class="message-html" bind:this={htmlEl}>
					{@html responseHtml}
				</div>
				<ButtonGroup>
					<Button color="input" on:click={handleCopy} size="small">
						{#snippet start()}
							<IconClipboard size={14} />
						{/snippet}
						Copy
					</Button>
					<Button color="input" on:click={addToEditor} size="small">
						{#snippet start()}
							<IconFileEarmark size={14} />
						{/snippet}
						Add to Editor
					</Button>
				</ButtonGroup>
			{/if}
		</div>
	</div>
</div>

<style lang="scss">
	.message-wrap {
		padding: 20px 25px;
		display: flex;
	}
	.message-wrap.ai {
		background-color: #fafafa;
	}
	.message {
		padding: 0 12px;
	}
	.message-input {
		line-height: 28px;
	}
	.error {
		color: var(--red);
		font-weight: 600;
		font-size: 14px;
	}

	.message-html {
		line-height: 28px;

		:global(h1),
		:global(h2),
		:global(h3),
		:global(h4),
		:global(h5) {
			margin-top: 0;
			margin-bottom: 15px;
		}

		:global(h1) {
			font-size: 1.6rem;
		}

		:global(h2) {
			font-size: 1.4rem;
		}

		:global(h3) {
			font-size: 1.3rem;
		}
		:global(h4) {
			font-size: 1.2rem;
		}
		:global(h5) {
			font-size: 1.1rem;
		}
		:global(h6) {
			font-size: 1rem;
		}

		:global(p) {
			margin-top: 0;
		}

		:global(ul),
		:global(ol) {
			padding-left: 30px;
		}

		:global(li p) {
			margin: 0;
		}

		:global(a) {
			color: var(--link);
			text-decoration: underline;
		}
	}
</style>
