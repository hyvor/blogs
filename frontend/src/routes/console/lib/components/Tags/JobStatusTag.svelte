<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { JobStatus } from '../../types';
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
	{status.toUpperCase()}
</Tag>
