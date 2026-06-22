<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Tag, Tooltip } from '@hyvor/design/components';
	import IconCheck from '@hyvor/icons/IconCheck';
	import IconHourglass from '@hyvor/icons/IconHourglass';
	import IconDot from '@hyvor/icons/IconDot';

	import type { PostVariant } from '../../../../lib/types';
	import { languagesStore } from '../../../../lib/stores/languagesStore';

	interface Props {
		variant: PostVariant;
		size?: 'x-small' | 'small' | 'medium' | 'large';
		[key: string]: any;
	}

	let { variant, size = "small", ...rest }: Props = $props();

	let language = $derived($languagesStore.find((v) => v.id === variant.language_id));

	let tooltip = $state('');
	let icon: any = $state();
	let iconProps = $state({});

	run(() => {
		iconProps = {};

		if (language) {
			if (variant.status === 'published') {
				icon = IconCheck;
				tooltip = `${language.name} - Published`;
			} else if (variant.status === 'draft') {
				icon = IconDot;
				tooltip = `${language.name} - Draft`;
			} else if (variant.status === 'scheduled') {
				icon = IconHourglass;
				tooltip = `${language.name} - Scheduled`;
			}
		}
	});
</script>

{#if language}
	<Tooltip text={tooltip} position="bottom">
		<Tag size={size} interactive color="default" outline {...rest}>
			{language.code}
			{#snippet end()}
				{@const SvelteComponent = icon}
				<SvelteComponent size={10} {...iconProps} />
			{/snippet}
		</Tag>
	</Tooltip>
{/if}
