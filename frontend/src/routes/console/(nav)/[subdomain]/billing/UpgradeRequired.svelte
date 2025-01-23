<script lang="ts">
	import IconArrowUpCircle from '@hyvor/icons/IconArrowUpCircle';
	import type { SubscriptionPlan } from '../../../lib/types';
	import { Button } from '@hyvor/design/components';
	import { consoleUrl } from '../../../lib/consoleUrl';
	import { minPlanCheck } from './minPlanCheck';
	import type { Snippet } from 'svelte';

	interface Props {
		minPlan: SubscriptionPlan;
		trialAllowed?: boolean;
		allow?: boolean;
		children: Snippet;
		upgradeText: Snippet;
	}

	let { minPlan, trialAllowed = false, allow = false, children, upgradeText }: Props = $props();
	let hasMinPlan = $derived(minPlanCheck(minPlan, trialAllowed));
</script>

{#if hasMinPlan || allow}
	{@render children()}
{:else}
	<div class="upgrade-required">
		<div class="upgrade-inner">
			<div class="upgrade-required-title">
				<IconArrowUpCircle />
				<span>Upgrade Required</span>
			</div>

			<div class="upgrade-required-content">
				{@html upgradeText}
			</div>

			<div class="upgrade-cta">
				<Button as="a" href={consoleUrl('/billing')}>Upgrade Now</Button>
			</div>
		</div>
	</div>
{/if}

<style>
	.upgrade-required {
		padding: 20px;
		width: 100%;
		height: 100%;
		flex: 1;
	}
	.upgrade-inner {
		padding: 20px 25px;
		background-color: var(--blue-light);
		border-radius: 20px;
		width: 550px;
		max-width: 100%;
		margin: auto;
	}
	.upgrade-required-title {
		font-size: 1.2rem;
		font-weight: 600;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.upgrade-required-title :global(svg) {
		margin-right: 8px;
	}
	.upgrade-required-content {
		margin-top: 20px;
		text-align: center;
		line-height: 20px;
	}
	.upgrade-cta {
		margin-top: 20px;
		text-align: center;
	}
</style>
