<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Tag, Tooltip } from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconDot from '@hyvor/icons/IconDot';

	import type { PostVariantSummary } from '../../../../lib/types';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import { getI18n } from '../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		variant: PostVariantSummary;
		size?: 'x-small' | 'small' | 'medium' | 'large';
		[key: string]: any;
	}

	let { variant, size = 'small', ...rest }: Props = $props();

	let language = $derived($languagesStore.find((v) => v.id === variant.language_id));

	let tooltip = $state('');
	let icon: any = $state();
	let iconProps = $state({});

	run(() => {
		iconProps = {};

		if (language) {
			if (variant.status === 'published') {
				icon = IconCheck;
				tooltip = i18n.t('console.posts.langTag.published', { language: language.name });
			} else if (variant.status === 'draft') {
				icon = IconDot;
				tooltip = i18n.t('console.posts.langTag.draft', { language: language.name });
			} else if (variant.status === 'scheduled') {
				icon = IconHourglass;
				tooltip = i18n.t('console.posts.langTag.scheduled', { language: language.name });
			}
		}
	});
</script>

{#if language}
	<Tooltip text={tooltip} position="bottom">
		<Tag {size} interactive color="default" outline {...rest}>
			{language.code}
			{#snippet end()}
				{@const SvelteComponent = icon}
				<SvelteComponent size={10} {...iconProps} />
			{/snippet}
		</Tag>
	</Tooltip>
{/if}
