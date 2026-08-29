<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import type { Tag } from '../../../../lib/types';
	import IconLock from '@hyvor/icons/IconLock';

	interface Props {
		tag: Tag;
		small?: boolean;
	}

	let { tag, small = false }: Props = $props();

	let variant = $derived(tag.variants.find((v) => v.language_id === $primaryLanguageStore.id));
</script>

<span class="tag-name">
	<span class="hash">#</span>{variant?.name || 'Unnamed'}
	{#if tag.is_private}
		<Tooltip text="Private tag">
			<IconLock size={small ? 10 : 12} />
		</Tooltip>
	{/if}
</span>

<style>
	.tag-name {
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}
	.hash {
		color: color-mix(in srgb, var(--text-light) 60%, transparent 40%);
	}
</style>
