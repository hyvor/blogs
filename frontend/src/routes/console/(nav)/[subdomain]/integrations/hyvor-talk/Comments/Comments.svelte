<script lang="ts">
	import { Button, CodeBlock, Label, Link, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddCommentsEmbedCode from './AddCommentsEmbedCode.svelte';
	import ConfiguredTag from '../ConfiguredTag.svelte';
	import { blogStore } from '../../../../../lib/stores/blogStore';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();
	const T = i18n.T;

	interface Props {
		websiteId: number;
	}

	let { websiteId }: Props = $props();

	let code = $derived(
		`<` +
			`script async src="https://talk.hyvor.com/embed/embed.js" type="module"></` +
			`script>
<hyvor-talk-comments 
    website-id="${websiteId}" 
    page-id="{{ _post.id }}"
    page-url="{{ _post.url }}"
></hyvor-talk-comments>`
	);

	let adding = $state(false);

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	{#snippet label()}
		<Label>
			{i18n.t('console.integrations.hyvorTalk.comments.label')}
			<ConfiguredTag
				configured={$blogStore.comments_code?.includes('<hyvor-talk-comments') || false}
			/>
		</Label>
	{/snippet}

	<p style="margin-top:0;">
		<T
			key="console.integrations.hyvorTalk.comments.description"
			params={{
				link: {
					element: 'a',
					props: {
						href: consoleUrlWithBlog('/settings/comments'),
						class: 'hds-link'
					}
				}
			}}
		/>
	</p>

	<CodeBlock {code} />

	<div>
		<Button size="small" on:click={() => (adding = true)}>
			{i18n.t('console.integrations.hyvorTalk.comments.addTo')}
		</Button>

		<Button size="small" color="input" on:click={() => handleCopy()}>
			{i18n.t('console.integrations.hyvorTalk.copyCode')}
		</Button>
	</div>

	<p>
		<T
			key="console.integrations.hyvorTalk.themeFilesNote"
			params={{
				link: {
					element: 'a',
					props: {
						href: 'https://talk.hyvor.com/docs/comments',
						target: '_blank',
						class: 'hds-link'
					}
				}
			}}
		/>
	</p>
</SplitControl>

{#if adding}
	<AddCommentsEmbedCode bind:open={adding} {code} />
{/if}
