<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import type { Tag } from '../../../../lib/types';
	import { IconLock } from '@hyvor/icons';

	export let tag: Tag;
	export let small = false;

	$: variant = tag.variants.find((v) => v.language_id === $primaryLanguageStore.id);
</script>

<span>
	{variant?.name || 'Unnamed'}
	{#if tag.is_private}
		<Tooltip text="Private tag">
			<IconLock size={small ? 10 : 12} />
		</Tooltip>
	{/if}
</span>

<style>
	span {
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}
</style>
