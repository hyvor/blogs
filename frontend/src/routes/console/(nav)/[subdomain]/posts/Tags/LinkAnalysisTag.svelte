<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { calculateLinkAnalysis, getCountsByStatus } from '../../../../lib/links/links';
	import type { PostVariant } from '../../../../lib/types';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import IconQuestionCircle from '@hyvor/icons/IconQuestionCircle';
	import IconQuestionCircleFill from '@hyvor/icons/IconQuestionCircleFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';

	interface Props {
		postVariant: PostVariant;
	}

	let { postVariant }: Props = $props();
	const linkAnalysis = calculateLinkAnalysis(postVariant);
	const counts = getCountsByStatus(linkAnalysis);
</script>

{#if counts.loading > 0}
	<Tooltip text="Link analysis outdated. Open the post to analyze again">
		<IconQuestionCircleFill size={12} />
	</Tooltip>
{:else if counts.broken > 0}
	<Tooltip text="Broken links found">
		<IconXCircleFill size={12} style="color:var(--red)" />
	</Tooltip>
{:else if counts.redirect > 0}
	<Tooltip text="Redirect links found">
		<IconExclamationCircleFill size={12} style="color:var(--orange)" />
	</Tooltip>
{:else}
	<Tooltip text="All links are healthy">
		<IconCheckCircleFill size={12} color="var(--green)" />
	</Tooltip>
{/if}
