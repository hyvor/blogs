<script lang="ts">
	import { Button, CodeBlock, Label, Link, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddCommentsEmbedCode from './AddCommentsEmbedCode.svelte';
	import ConfiguredTag from '../ConfiguredTag.svelte';
	import { blogStore } from '../../../../../lib/stores/blogStore';

	export let websiteId: number;

	$: code =
		`<` +
		`script async src="https://talk.hyvor.com/embed/embed.js" type="module"></` +
		`script>
<hyvor-talk-comments 
    website-id="${websiteId}" 
    page-id="{{ _post.id }}"
    page-url="{{ _post.url }}"
></hyvor-talk-comments>`;

	let adding = false;

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	<Label slot="label">
		Comments <ConfiguredTag
			configured={$blogStore.comments_code?.includes('<hyvor-talk-comments') || false}
		/>
	</Label>

	<p style="margin-top:0;">
		Add the comments code to <Link
			style="display:inline;"
			underline
			href={consoleUrlWithBlog('/settings/comments')}>Comments Embed Code</Link
		> setting to load the Hyvor Talk comments on all posts.
	</p>

	<CodeBlock {code} />

	<div>
		<Button size="small" on:click={() => (adding = true)}>Add to "Comments Embed Code"</Button>

		<Button size="small" color="input" on:click={() => handleCopy()}>Copy code</Button>
	</div>

	<p>
		You can also add it directly into your theme files. Feel free to customize the code (see <Link
			underline
			href="https://talk.hyvor.com/docs/comments"
			target="_blank">Hyvor Talk docs</Link
		>).
	</p>
</SplitControl>

{#if adding}
	<AddCommentsEmbedCode bind:open={adding} {code} />
{/if}
