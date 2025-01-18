<script lang="ts">
	import { run } from 'svelte/legacy';

	import { Button, Loader, Tag, toast } from '@hyvor/design/components';
	import type { SubscriptionFrequency, SubscriptionPlan } from '../../../../lib/types';
	import { subscriptionStore } from '../../../../lib/stores/subscriptionStore';
	import { createSubscription } from '../paddleActions';

	interface Props {
		name: SubscriptionPlan;
		frequency: SubscriptionFrequency;
	}

	let { name, frequency }: Props = $props();

	let checkoutLoading = $state(false);

	function getPriceFromPlan(type: SubscriptionPlan) {
		return {
			starter: 12,
			growth: 40,
			premium: 125
		}[type];
	}

	let price: number = $state();
	let isCurrent = $state(false);
	run(() => {
		price = getPriceFromPlan(name);
		if (frequency === 'yearly') price *= 10;

		isCurrent =
			$subscriptionStore?.plan === name &&
			(($subscriptionStore?.isAnnual && frequency === 'yearly') ||
				(!$subscriptionStore?.isAnnual && frequency === 'monthly'));
	});

	async function handleCancel() {
		// todo: handle cancel
	}

	async function handleSwitch() {
		// todo: handle switch
	}

	async function handleUpgrade() {
		checkoutLoading = true;

		createSubscription(name, frequency)
			.then(({ redirect }) => {
				window.location.href = redirect;
			})
			.catch((e) => {
				checkoutLoading = false;
				toast.error(e.message);
			});
	}
</script>

<div class="plan" class:current={isCurrent}>
	<div class="plan-left">
		<span class="plan-name">{name}</span>

		{#if isCurrent}
			<Tag color="accent" size="x-small">CURRENT</Tag>
		{/if}
	</div>
	<div class="plan-right">
		<span class="plan-price"
			>€{price}<span class="per-month">
				/{frequency === 'monthly' ? 'month' : 'year'}
			</span></span
		>
		<div class="plan-right-button-wrap">
			{#if isCurrent}
				<Button color="red" size="small" on:click={handleCancel}>Cancel</Button>
			{:else}
				<Button
					size="small"
					on:click={() => ($subscriptionStore ? handleSwitch() : handleUpgrade())}
				>
					{$subscriptionStore ? 'Switch' : 'Upgrade'}
				</Button>
			{/if}
		</div>
	</div>
</div>

{#if checkoutLoading}
	<div class="checkout-loading">
		<Loader full colorTrack="transparent" color="white" size="large" />
	</div>
{/if}

<style lang="scss">
	.checkout-loading {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0, 0, 0, 0.7);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 1000;
	}

	.plan {
		padding: 15px;
		border-radius: 20px;
		background: var(--input);
		margin-bottom: 10px;
		display: flex;
		&.current {
			box-shadow: 0 0 2px 0px #886c6b;
		}
		.plan-left {
			flex: 1;
			align-items: center;
			display: flex;
		}
		.plan-right {
			display: flex;
			align-items: center;
			.plan-right-button-wrap {
				min-width: 65px;
				text-align: center;
			}
		}

		.plan-name {
			font-size: 16px;
			font-weight: 600;
			text-transform: capitalize;
			margin-right: 5px;
		}
		.plan-price {
			font-size: 14px;
			font-weight: 600;
			margin-right: 10px;
			.per-month {
				color: var(--text-light);
				font-weight: normal;
				font-size: 11px;
			}
		}
	}
</style>
