<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { getCountsByStatus } from '../../../../lib/links/links';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import IconQuestionCircle from '@hyvor/icons/IconQuestionCircle';
	import IconQuestionCircleFill from '@hyvor/icons/IconQuestionCircleFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		linkAnalysis: Record<string, number>;
	}

	let { linkAnalysis }: Props = $props();
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
