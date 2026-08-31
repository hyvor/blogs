<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { calculateLinkAnalysis, getCountsByStatus } from '../../../../lib/links/links';
	import type { PostVariant } from '../../../../lib/types';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import IconQuestionCircle from '@hyvor/icons/IconQuestionCircle';
	import IconQuestionCircleFill from '@hyvor/icons/IconQuestionCircleFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';
	import { getI18n } from '../../../../lib/i18n';

	interface Props {
		postVariant: PostVariant;
	}

	let { postVariant }: Props = $props();
	const i18n = getI18n();
	const linkAnalysis = calculateLinkAnalysis(postVariant);
	const counts = getCountsByStatus(linkAnalysis);
</script>

{#if counts.loading > 0}
	<Tooltip text={i18n.t('console.posts.linkAnalysis.outdated')}>
		<IconQuestionCircleFill size={12} />
	</Tooltip>
{:else if counts.broken > 0}
	<Tooltip text={i18n.t('console.posts.linkAnalysis.broken')}>
		<IconXCircleFill size={12} style="color:var(--red)" />
	</Tooltip>
{:else if counts.redirect > 0}
	<Tooltip text={i18n.t('console.posts.linkAnalysis.redirect')}>
		<IconExclamationCircleFill size={12} style="color:var(--orange)" />
	</Tooltip>
{:else}
	<Tooltip text={i18n.t('console.posts.linkAnalysis.healthy')}>
		<IconCheckCircleFill size={12} color="var(--green)" />
	</Tooltip>
{/if}
