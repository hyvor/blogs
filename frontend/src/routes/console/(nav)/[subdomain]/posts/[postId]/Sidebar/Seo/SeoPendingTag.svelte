<script>
	import { Tooltip } from '@hyvor/design/components';
	import IconQuestionCircleFill from '@hyvor/icons/IconQuestionCircleFill';
	import { getI18n } from '../../../../../../lib/i18n';

	const i18n = getI18n();
</script>

<Tooltip text={i18n.t('console.postEditor.seo.pendingTooltip')} maxWidth={200}>
	<IconQuestionCircleFill size={12} style="color: var(--text-light);" />
</Tooltip>
