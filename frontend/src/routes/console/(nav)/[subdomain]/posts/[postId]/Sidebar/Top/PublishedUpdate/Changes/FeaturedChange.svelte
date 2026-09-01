<script lang="ts">
	import IconArrowRight from '@hyvor/icons/IconArrowRight';
	import { getI18n } from '../../../../../../../../lib/i18n';

	const i18n = getI18n();

	interface Props {
		old: boolean;
		diff: boolean;
	}

	let { old, diff }: Props = $props();

	const current = $derived(!old);

	const yesNo = (v: boolean) =>
		v ? i18n.t('console.postEditor.update.yes') : i18n.t('console.postEditor.update.no');
</script>

<div class="wrap">
	{#if diff}
		<span class="before">
			{yesNo(old)}
		</span>
		<IconArrowRight />
	{/if}

	<span class="after">
		{yesNo(current)}
	</span>
</div>

<style>
	.wrap {
		display: flex;
		align-items: center;
	}
	.wrap :global(svg) {
		margin: 0 15px;
		color: var(--text-light);
	}
	.after {
		font-weight: 600;
	}
</style>
