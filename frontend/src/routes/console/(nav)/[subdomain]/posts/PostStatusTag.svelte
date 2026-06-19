<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { PostStatus } from '../../../lib/types';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconDot from '@hyvor/icons/IconDot';

	interface Props {
		status: PostStatus;
		size?: 'x-small' | 'small' | 'medium';
	}

	let { status, size = 'small' }: Props = $props();

	let color = $derived(
		{
			draft: 'orange',
			published: 'green',
			scheduled: 'blue'
		}[status] as any
	);

	let icon = $derived(
		{
			draft: IconDot,
			published: IconCheck,
			scheduled: IconHourglass
		}[status] as any
	);
</script>

<Tag {color} {size}>
	{#snippet start()}
		{@const SvelteComponent = icon}
		<SvelteComponent size={status === "scheduled" ? 10  : 12} />
	{/snippet}

	{status.toUpperCase()}
</Tag>
