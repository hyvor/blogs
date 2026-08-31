<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { PostStatus } from '../../../lib/types';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconJournalText from '@hyvor/icons/IconJournalText';
	import { getI18n } from '../../../lib/i18n';

	const STATUS_KEYS = {
		draft: 'console.posts.status.draft',
		published: 'console.posts.status.published',
		scheduled: 'console.posts.status.scheduled'
	} as const;

	interface Props {
		status: PostStatus;
		size?: 'x-small' | 'small' | 'medium';
	}

	let { status, size = 'small' }: Props = $props();

	const i18n = getI18n();

	let color = $derived(
		{
			draft: 'orange',
			published: 'green',
			scheduled: 'blue'
		}[status] as any
	);

	let icon = $derived(
		{
			draft: IconJournalText,
			published: IconCheck,
			scheduled: IconHourglass
		}[status] as any
	);
</script>

<Tag {color} {size}>
	{#snippet start()}
		{@const SvelteComponent = icon}
		<SvelteComponent size={12} />
	{/snippet}

	{i18n.t(STATUS_KEYS[status]).toUpperCase()}
</Tag>
