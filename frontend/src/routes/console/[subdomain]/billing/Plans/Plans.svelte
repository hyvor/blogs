<script lang="ts">
	import { Link, Switch, Tag } from '@hyvor/design/components';
	import type { SubscriptionFrequency, SubscriptionPlan } from '../../../lib/types';
	import PlanNotices from './PlanNotices.svelte';
	import { IconBoxArrowUpRight } from '@hyvor/icons';
	import Plan from './Plan.svelte';
	import { subscriptionStore } from '../../../lib/stores/subscriptionStore';

	let frequency: SubscriptionFrequency = $subscriptionStore?.frequency || 'monthly';

	const planNames = ['starter', 'growth', 'premium', 'team'] as SubscriptionPlan[];
</script>

<div class="title">
	<div class="left">Plans</div>

	<div class="right">
		<span class="yearly">
			Pay Yearly
			<span class="yearly-tag">
				<Tag color="blue" size="x-small">17% off</Tag>
			</span>
		</span>

		<Switch
			checked={frequency === 'yearly'}
			on:change={() => {
				frequency = frequency === 'monthly' ? 'yearly' : 'monthly';
			}}
		/>
	</div>
</div>

<div class="content">
	<PlanNotices />

	<div class="plans">
		{#each planNames as plan}
			<Plan name={plan} {frequency} />
		{/each}
	</div>

	<div class="section-desc">
		Prices are shown in EUR, including all VAT charges <br />
		<div>
			<Link href="/pricing" target="_blank">
				<span>Pricing</span>
				<IconBoxArrowUpRight slot="end" />
			</Link>
		</div>
	</div>
</div>

<style>
	.title {
		margin-bottom: 15px;
		display: flex;
		align-items: center;
	}
	.title .left {
		font-size: 20px;
		font-weight: 600;
		flex: 1;
	}

	.yearly {
		font-size: 14px;
		font-weight: 500;
		color: var(--text-light);
		position: relative;
		margin-right: 25px;
	}

	.yearly-tag {
		position: absolute;
		bottom: 100%;
		margin-bottom: -1px;
		right: -20px;
	}

	.section-desc {
		font-size: 14px;
		text-align: center;
		padding: 10px;
		color: var(--text-light);
	}
</style>
