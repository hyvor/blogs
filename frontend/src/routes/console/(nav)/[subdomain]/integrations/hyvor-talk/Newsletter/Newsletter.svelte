<script lang="ts">
	import { Button, CodeBlock, Label, Link, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddNewsletterCode from './AddNewsletterCode.svelte';
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
			`script async src="https://talk.hyvor.com/embed/newsletter.js" type="module"><` +
			`/script>
<hyvor-talk-newsletter website-id="${websiteId}"></hyvor-talk-newsletter>`
	);

	let adding = $state(false);

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	{#snippet label()}
		<Label>
			{i18n.t('console.integrations.hyvorTalk.newsletter.label')}
			<ConfiguredTag
				configured={$blogStore.newsletter_code?.includes('<hyvor-talk-newsletter') || false}
			/>
		</Label>
	{/snippet}

	<p style="margin-top:0;">
		<T
			key="console.integrations.hyvorTalk.newsletter.description"
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
			{i18n.t('console.integrations.hyvorTalk.newsletter.addTo')}
		</Button>

		<Button size="small" color="input" on:click={handleCopy}>
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
						href: 'https://talk.hyvor.com/docs/newsletters',
						target: '_blank',
						class: 'hds-link'
					}
				}
			}}
		/>
	</p>
</SplitControl>

{#if adding}
	<AddNewsletterCode bind:open={adding} {code} />
{/if}
