<script lang="ts">
	import { Button, CodeBlock, Label, Link, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddMembershipsCode from './AddMembershipsCode.svelte';
	import ConfiguredTag from '../ConfiguredTag.svelte';
	import { blogStore } from '../../../../../lib/stores/blogStore';

	export let websiteId: number;

	$: code =
		`<` +
		`script async src="https://talk.hyvor.com/embed/memberships.js" type="module"><` +
		`/script>
<hyvor-talk-memberships website-id="${websiteId}"></hyvor-talk-memberships>`;

	let adding = false;

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	<Label slot="label">
		Memberships <ConfiguredTag
			configured={$blogStore.code_foot?.includes('<hyvor-talk-memberships') || false}
		/>
	</Label>

	<p style="margin-top:0;">
		After setting up Memberships in the <Link
			href={'https://talk.hyvor.com/console/' + websiteId + '/settings/memberships'}
		>
			Hyvor Talk Console
		</Link>, add the following code to <Link href={consoleUrlWithBlog('/settings/code')}>
			Foot Code
		</Link> setting to load Hyvor Talk Memberships on all pages.
	</p>

	<CodeBlock {code} />

	<div>
		<Button size="small" on:click={() => (adding = true)}>Append to "Foot Code"</Button>
		<Button size="small" color="input" on:click={handleCopy}>Copy code</Button>
	</div>
</SplitControl>

{#if adding}
	<AddMembershipsCode bind:open={adding} {code} />
{/if}
