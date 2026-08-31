<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { JobStatus } from '../../types';
	import { getI18n } from '../../i18n';

	const i18n = getI18n();

	const STATUS_KEYS = {
		pending: 'console.tools.jobStatus.pending',
		completed: 'console.tools.jobStatus.completed',
		failed: 'console.tools.jobStatus.failed'
	} as const;

	interface Props {
		status: JobStatus;
	}

	let { status }: Props = $props();

	const color = {
		pending: 'blue',
		completed: 'green',
		failed: 'red'
	}[status] as any;
</script>

<Tag {color} size="small">
	{i18n.t(STATUS_KEYS[status]).toUpperCase()}
</Tag>
