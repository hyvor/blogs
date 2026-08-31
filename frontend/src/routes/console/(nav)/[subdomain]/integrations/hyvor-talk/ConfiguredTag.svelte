<script lang="ts">
	import { Tag, Tooltip } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		configured: boolean;
	}

	let { configured }: Props = $props();
</script>

<Tooltip text={i18n.t('console.integrations.hyvorTalk.configuredTooltip')}>
	{#if configured}
		<Tag size="small" color="green">{i18n.t('console.integrations.hyvorTalk.configured')}</Tag>
	{:else}
		<Tag size="small">{i18n.t('console.integrations.hyvorTalk.notConfigured')}</Tag>
	{/if}
</Tooltip>
