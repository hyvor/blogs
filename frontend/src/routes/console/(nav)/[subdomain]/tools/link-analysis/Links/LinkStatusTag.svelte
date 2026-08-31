<script lang="ts">
	import { Tag, Tooltip } from '@hyvor/design/components';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';

	import { getStatusType } from '../../../../../lib/links/links';
	import type { LinkAnalysisIgnoreReason, LinkAnalysisStatusType } from '../../../../../lib/types';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';
	import { getI18n } from '../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		status?: number;
		type?: LinkAnalysisStatusType;
		ignoreReason?: LinkAnalysisIgnoreReason | null;
		isAnchor?: boolean;
		showTooltip?: boolean;
	}

	let { status, type, ignoreReason, isAnchor = false, showTooltip = true }: Props = $props();

	let statusType = $derived(type || (status !== undefined && getStatusType(status)));
	let statusDisplay = $state('');
	let tooltip = $state('');
	let color: any = $state('default');

	function getReadableIgnoreReason(reason: LinkAnalysisIgnoreReason): string {
		switch (reason) {
			case 'known_firewall':
				return i18n.t('console.tools.linkAnalysis.ignoreReason.known_firewall');
			case 'robots_txt':
				return i18n.t('console.tools.linkAnalysis.ignoreReason.robots_txt');
			case 'internal_error':
				return i18n.t('console.tools.linkAnalysis.ignoreReason.internal_error');
		}
	}

	$effect(() => {
		if (statusType === 'ok') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.ok');

			if (isAnchor) {
				tooltip = i18n.t('console.tools.linkAnalysis.tooltip.headingIdFound');
			} else if (status) {
				tooltip = i18n.t('console.tools.linkAnalysis.tooltip.okStatus', { status });
			} else {
				tooltip = i18n.t('console.tools.linkAnalysis.tooltip.ok');
			}

			color = 'green';
		} else if (statusType === 'redirect') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.redirect');
			tooltip = status
				? i18n.t('console.tools.linkAnalysis.tooltip.redirectStatus', { status })
				: i18n.t('console.tools.linkAnalysis.tooltip.redirect');
			color = 'blue';
		} else if (statusType === 'broken') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.broken');

			if (isAnchor) {
				tooltip = i18n.t('console.tools.linkAnalysis.tooltip.headingIdNotFound');
			} else if (status !== undefined) {
				tooltip =
					status === 0
						? i18n.t('console.tools.linkAnalysis.tooltip.brokenConnection')
						: i18n.t('console.tools.linkAnalysis.tooltip.brokenStatus', { status });
			} else {
				tooltip = i18n.t('console.tools.linkAnalysis.tooltip.broken');
			}

			color = 'red';
		} else if (statusType === 'risky') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.risky');
			tooltip = status
				? i18n.t('console.tools.linkAnalysis.tooltip.riskyStatus', { status })
				: i18n.t('console.tools.linkAnalysis.tooltip.risky');
			color = 'orange';
		} else if (statusType === 'ignored') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.ignored');
			tooltip = ignoreReason
				? i18n.t('console.tools.linkAnalysis.tooltip.ignoredWithReason', {
						reason: getReadableIgnoreReason(ignoreReason)
					})
				: i18n.t('console.tools.linkAnalysis.tooltip.ignored');
			color = 'default';
		} else if (statusType === 'error') {
			statusDisplay = i18n.t('console.tools.linkAnalysis.status.error');
			tooltip = i18n.t('console.tools.linkAnalysis.tooltip.error');
			color = 'red';
		}
	});
</script>

<Tooltip text={tooltip}>
	<Tag size="small" {color}>
		{statusDisplay}

		{#snippet end()}
			<span class="icon">
				{#if statusType === 'ok'}
					<IconCheckCircleFill size={12} />
				{:else if statusType === 'redirect'}
					<IconSignTurnSlightRight size={12} />
				{:else if statusType === 'broken'}
					<IconXCircleFill size={12} />
				{:else if statusType === 'risky'}
					<IconExclamationCircleFill size={12} />
				{:else if statusType === 'ignored'}
					<IconEyeSlashFill size={12} />
				{:else if statusType === 'error'}
					<IconXCircleFill size={12} />
				{/if}
			</span>
		{/snippet}
	</Tag>
</Tooltip>

<style>
	.icon {
		display: inline-flex;
		align-items: center;
	}
</style>
