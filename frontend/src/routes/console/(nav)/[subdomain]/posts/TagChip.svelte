<script lang="ts">
	import { Tag } from '@hyvor/design/components';
	import type { Snippet } from 'svelte';
	import type { Tag as TagType } from '../../../lib/types';
	import TagName from '../settings/tags/TagName.svelte';

	interface Props {
		tag: TagType | { id?: undefined; name: string; is_private: boolean };
		size?: 'x-small' | 'small' | 'medium';
		end?: Snippet;
	}

	let { tag, size = 'small', end }: Props = $props();
</script>

<Tag {size} bg="#f1f1f1" {end}>
	<TagName {tag} small />
</Tag>
