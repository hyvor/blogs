<script lang="ts">
	import { SplitControl, Text } from '@hyvor/design/components';
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		canonicalUrlOld: string | null;
		canonicalUrlNew: string | null;
		diff: boolean;
	}

	let { canonicalUrlOld, canonicalUrlNew, diff }: Props = $props();
</script>

<SplitControl label={i18n.t('console.postEditor.update.canonicalUrl')}>
	<div class="wrap">
		{#if diff}
			<span class="before">
				{#if canonicalUrlOld}
					{canonicalUrlOld}
				{:else}
					<Text small light>{i18n.t('console.postEditor.update.empty')}</Text>
				{/if}
			</span>
			<IconArrowRight />
		{/if}

		<span class="after">
			{#if canonicalUrlNew}
				{canonicalUrlNew}
			{:else}
				<Text small light>{i18n.t('console.postEditor.update.empty')}</Text>
			{/if}
		</span>
	</div>
</SplitControl>

<style>
	.wrap {
		display: flex;
		align-items: center;
	}
	.wrap :global(svg) {
		margin: 0 15px;
	}
	.after {
		font-weight: 600;
	}
</style>
