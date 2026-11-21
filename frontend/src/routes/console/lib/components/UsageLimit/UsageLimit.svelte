<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import { getConfig } from '../../config';
	import { getI18n } from '../../i18n';
	import UsageLimitModal from './UsageLimitModal.svelte';

	const i18n = getI18n();

	interface Props {
		/** current usage */
		used: number;
		/** the limit. -1 / undefined = unlimited (nothing is rendered) */
		limit: number | undefined | null;
		/**
		 * The license property this limit is about (the key in the license, ex: "languages").
		 * It is passed to the billing iframe so that the matching plans and add-ons are highlighted.
		 */
		focus: string;
		/** Label before the numbers. Defaults to "Usage" */
		label?: string;
		/** Title of the add-on modal. Defaults to a generic title */
		modalTitle?: string;
	}

	let { used, limit, focus, label, modalTitle }: Props = $props();

	let show = $state(false);

	// limits and add-ons are a cloud (billing) feature
	const visible = $derived(getConfig().deployment === 'cloud' && limit != null && limit >= 0);
	const reached = $derived(limit != null && limit >= 0 && used >= limit);
</script>

{#if visible}
	<div class="usage-limit">
		<span class="text" class:reached>
			{label ?? i18n.t('console.common.usageLimit.usage')}: {used}/{limit}
		</span>
		<Button size="small" color="input" on:click={() => (show = true)}>
			{i18n.t('console.common.usageLimit.upgrade')}
		</Button>
	</div>

	{#if show}
		<UsageLimitModal
			bind:show
			{focus}
			title={modalTitle ?? i18n.t('console.common.usageLimit.modalTitle')}
		/>
	{/if}
{/if}

<style>
	.usage-limit {
		display: inline-flex;
		align-items: center;
		gap: 10px;
	}
	.text {
		font-size: 14px;
		color: var(--text-light);
	}
	.text.reached {
		color: var(--red);
		font-weight: 600;
	}
</style>
