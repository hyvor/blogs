<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import type { Tag } from '../../../../lib/types';
	import IconLock from '@hyvor/icons/IconLock';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		tag: Tag | { id: undefined; name: string; is_private: boolean };
		small?: boolean;
	}

	let { tag, small = false }: Props = $props();

	let name = $derived(
		(tag.id !== undefined
			? tag.variants.find((v) => v.language_id === $primaryLanguageStore.id)?.name
			: tag.name) || 'Unnamed'
	);
</script>

<span class="tag-name">
	{#if tag.is_private}
		<Tooltip text={i18n.t('console.settings.tags.privateTag')}>
			<IconLock size={small ? 9 : 12} />
		</Tooltip>
	{:else}
		<span class="hash">#</span>
	{/if}
	{name}
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
