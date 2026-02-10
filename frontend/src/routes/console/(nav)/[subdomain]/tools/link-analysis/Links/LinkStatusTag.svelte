<script lang="ts">
	import { Tag, Tooltip } from '@hyvor/design/components';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import IconExclamationCircleFill from '@hyvor/icons/IconExclamationCircleFill';
	import IconEyeSlashFill from '@hyvor/icons/IconEyeSlashFill';
	import IconXCircleFill from '@hyvor/icons/IconXCircleFill';

	import { getStatusType } from '../../../../../lib/links/links';
	import type {
		LinkAnalysisIgnoreReason,
		LinkAnalysisStatusType
	} from '../../../../../lib/types';
	import IconSignTurnSlightRight from '@hyvor/icons/IconSignTurnSlightRight';

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
				return 'Known firewall';
			case 'robots_txt':
				return 'Blocked by robots.txt';
			case 'internal_error':
				return 'Internal error';
		}
	}

	$effect(() => {
		if (statusType === 'ok') {
			statusDisplay = 'OK';

			if (isAnchor) {
				tooltip = 'Heading ID found';
			} else if (status) {
				tooltip = 'OK - HTTP status ' + status;
			} else {
				tooltip = 'Link is OK';
			}

			color = 'green';
		} else if (statusType === 'redirect') {
			statusDisplay = 'Redirect';
			tooltip = status ? 'Redirect status ' + status : 'Redirecting to another page';
			color = 'blue';
		} else if (statusType === 'broken') {
			statusDisplay = 'Broken';

			if (isAnchor) {
				tooltip = 'Heading ID not found';
			} else if (status !== undefined) {
				tooltip =
					status === 0 ? 'Broken - Connection issue' : 'Broken - HTTP status ' + status;
			} else {
				tooltip = 'Link is broken';
			}

			color = 'red';
		} else if (statusType === 'risky') {
			statusDisplay = 'Risky';
			tooltip =
				'Link is risky, we recommend manually checking it' +
				(status ? ' - HTTP status ' + status : '');
			color = 'orange';
		} else if (statusType === 'ignored') {
			statusDisplay = 'Ignored';
			tooltip =
				'Link Ignored' +
				(ignoreReason ? ' (' + getReadableIgnoreReason(ignoreReason) + ')' : '');
			color = 'default';
		} else if (statusType === 'error') {
			statusDisplay = 'Error';
			tooltip = 'Error (on our side)';
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
