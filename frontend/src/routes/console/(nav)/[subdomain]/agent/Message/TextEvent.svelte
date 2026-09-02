<script lang="ts">
	import { getPurifiedHtmlFromMarkdown } from './html';

	interface Props {
		content: string;
	}

	let { content }: Props = $props();
</script>

{#if content}
	<div class="message-html">
		{@html getPurifiedHtmlFromMarkdown(content)}
	</div>
{/if}

<style>
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

		:global(p) {
			margin-top: 0;
		}

		:global(ul),
		:global(ol) {
			padding-left: 30px;
		}

		:global(a) {
			color: var(--link);
			text-decoration: underline;
		}
	}
</style>
