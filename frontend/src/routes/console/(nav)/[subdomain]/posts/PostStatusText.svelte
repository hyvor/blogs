<script lang="ts">
	import type { PostStatus } from '../../../lib/types';

	interface Props {
		status: PostStatus;
	}

	let { status }: Props = $props();

	let color = $derived(
		{
			draft: 'var(--orange)',
			published: 'var(--green)',
			scheduled: 'var(--blue)'
		}[status] as any
	);
</script>

<span style="color: {color}">{status}</span>

<style>
	span {
		text-transform: capitalize;
	}
</style>
