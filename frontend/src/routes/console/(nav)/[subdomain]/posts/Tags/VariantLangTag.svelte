<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Loader, Tag, Tooltip } from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconJournalText from '@hyvor/icons/IconJournalText';

	import type { PostVariant } from '../../../../lib/types';
	import { languagesStore } from '../../../../lib/stores/languagesStore';
	import { getI18n } from '../../../../lib/i18n';

	interface Props {
		variant: PostVariant;
		[key: string]: any;
	}

	let { variant, ...rest }: Props = $props();

	const i18n = getI18n();

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
				icon = IconJournalText;
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
		<Tag size="small" interactive color="default" {...rest}>
			{language.code}
			{#snippet end()}
				{@const SvelteComponent = icon}
				<SvelteComponent size={10} {...iconProps} />
			{/snippet}
		</Tag>
	</Tooltip>
{/if}
