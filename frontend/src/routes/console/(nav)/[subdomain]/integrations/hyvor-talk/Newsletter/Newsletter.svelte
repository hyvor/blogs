<script lang="ts">
	import { Button, CodeBlock, Label, Link, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddNewsletterCode from './AddNewsletterCode.svelte';
	import ConfiguredTag from '../ConfiguredTag.svelte';
	import { blogStore } from '../../../../../lib/stores/blogStore';

	export let websiteId: number;

	$: code =
		`<` +
		`script async src="https://talk.hyvor.com/embed/newsletter.js" type="module"><` +
		`/script>
<hyvor-talk-newsletter website-id="${websiteId}"></hyvor-talk-newsletter>`;

	let adding = false;

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	<Label slot="label">
		Newsletter <ConfiguredTag
			configured={$blogStore.newsletter_code?.includes('<hyvor-talk-newsletter') || false}
		/>
	</Label>

	<p style="margin-top:0;">
		Add the newsletter code to <Link
			style="display:inline;"
			underline
			href={consoleUrlWithBlog('/settings/comments')}>Newsletter Signup Form Code</Link
		> setting to load the Hyvor Talk newsletter form on places supported by your theme.
	</p>

	<CodeBlock {code} />

	<div>
		<Button size="small" on:click={() => (adding = true)}>
			Add to "Newsletter Signup Form Code"
		</Button>

		<Button size="small" color="input" on:click={handleCopy}>Copy code</Button>
	</div>

	<p>
		You can also add it directly into your theme files. Feel free to customize the code (see <Link
			underline
			href="https://talk.hyvor.com/docs/newsletters"
			target="_blank">Hyvor Talk docs</Link
		>).
	</p>
</SplitControl>

{#if adding}
	<AddNewsletterCode bind:open={adding} {code} />
{/if}
