<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import { primaryLanguageStore } from '../../../../lib/stores/languagesStore';
	import type { Tag } from '../../../../lib/types';
	import IconLock from '@hyvor/icons/IconLock';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		tag: Tag;
		small?: boolean;
	}

	let { tag, small = false }: Props = $props();

	let variant = $derived(tag.variants.find((v) => v.language_id === $primaryLanguageStore.id));
</script>

<span>
	{variant?.name || i18n.t('console.common.unnamed')}
	{#if tag.is_private}
		<Tooltip text={i18n.t('console.settings.tags.privateTag')}>
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
