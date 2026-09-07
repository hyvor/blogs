<script lang="ts">
	import { Callout } from '@hyvor/design/components';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();
</script>

<Callout type="danger">
	{i18n.t('console.agent.error')}
</Callout>
