<script lang="ts">
	import { Avatar, Tag } from '@hyvor/design/components';
	import type { Snippet } from 'svelte';
	import type { User } from '../../../lib/types';
	import { getPrimaryLanguage } from '../../../lib/stores/languagesStore';

	interface Props {
		user: User;
		size?: 'x-small' | 'small' | 'medium';
		end?: Snippet;
	}

	let { user, size = 'small', end }: Props = $props();

	const primaryLanguage = getPrimaryLanguage();
	let name = $derived(
		user.variants.find((v) => v.language_id === primaryLanguage.id)?.name || 'Unnamed'
	);
</script>

<Tag {size} style="padding: 4px 8px" bg="#f1f1f1" {end}>
	{#snippet start()}
		<Avatar src={user.picture_url || undefined} alt={name} size={size === 'x-small' ? 14 : 16} />
	{/snippet}
	{name}
</Tag>
