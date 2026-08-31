<script lang="ts">
	import { Button, CodeBlock, Label, SplitControl } from '@hyvor/design/components';
	import { consoleUrlWithBlog } from '../../../../../lib/consoleUrl';
	import AddMembershipsCode from './AddMembershipsCode.svelte';
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
			`script async src="https://talk.hyvor.com/embed/memberships.js" type="module"><` +
			`/script>
<hyvor-talk-memberships website-id="${websiteId}"></hyvor-talk-memberships>`
	);

	let adding = $state(false);

	function handleCopy() {
		navigator.clipboard.writeText(code);
	}
</script>

<SplitControl column>
	{#snippet label()}
		<Label>
			{i18n.t('console.integrations.hyvorTalk.memberships.label')}
			<ConfiguredTag
				configured={$blogStore.code_foot?.includes('<hyvor-talk-memberships') || false}
			/>
		</Label>
	{/snippet}

	<p style="margin-top:0;">
		<T
			key="console.integrations.hyvorTalk.memberships.description"
			params={{
				talkLink: {
					element: 'a',
					props: {
						href: 'https://talk.hyvor.com/console/' + websiteId + '/settings/memberships',
						target: '_blank',
						class: 'hds-link'
					}
				},
				codeLink: {
					element: 'a',
					props: { href: consoleUrlWithBlog('/settings/code'), class: 'hds-link' }
				}
			}}
		/>
	</p>

	<CodeBlock {code} />

	<div>
		<Button size="small" on:click={() => (adding = true)}>
			{i18n.t('console.integrations.hyvorTalk.memberships.appendTo')}
		</Button>
		<Button size="small" color="input" on:click={handleCopy}>
			{i18n.t('console.integrations.hyvorTalk.copyCode')}
		</Button>
	</div>
</SplitControl>

{#if adding}
	<AddMembershipsCode bind:open={adding} {code} />
{/if}
