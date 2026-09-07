<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { PostStatus } from '../../../lib/types';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconDot from '@hyvor/icons/IconDot';
	import { getI18n } from '../../../lib/i18n';

	const i18n = getI18n();

	const STATUS_KEYS = {
		draft: 'console.posts.status.draft',
		published: 'console.posts.status.published',
		scheduled: 'console.posts.status.scheduled'
	} as const;

	interface Props {
		status: PostStatus;
		size?: 'x-small' | 'small' | 'medium';
		showIcon?: boolean;
	}

	let { status, size = 'small', showIcon = true }: Props = $props();

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
		{#if showIcon}
			{@const SvelteComponent = icon}
			<SvelteComponent size={status === 'scheduled' ? 10 : 12} />
		{/if}
	{/snippet}

	{i18n.t(STATUS_KEYS[status]).toUpperCase()}
</Tag>
