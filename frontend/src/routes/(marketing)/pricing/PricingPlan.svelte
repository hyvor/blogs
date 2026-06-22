<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconCheckCircle from '@hyvor/icons/IconCheckCircle';

	interface Props {
		plan: 'personal' | 'starter' | 'growth' | 'premium';
		yearly: boolean;
		currency: string;
	}

	let { plan, yearly, currency }: Props = $props();

	const prices = {
		personal: 40,
		starter: 12,
		growth: 40,
		premium: 125
	};

	function getFeatures(): string[] {
		if (plan === 'personal') {
			return [
				'1 blog',
				'1 user',
				'1GB media storage',
				'Custom themes & domain',
				'Multi-language support',
				'All APIs included'
			];
		}
		if (plan === 'starter') {
			return [
				'All Personal features',
				'3 blogs',
				'5 users',
				'5GB media storage',
				'SEO Analysis'
			];
		}
		if (plan === 'growth') {
			return [
				'All Starter features',
				'10 blogs',
				'15 users',
				'150GB media storage',
				'Link Analysis',
				'100k AI tokens/month',
				'100k auto-translation chars/month'
			];
		}
		return [
			'All Growth features',
			'20 blogs',
			'50 users',
			'500GB media storage',
			'1m AI tokens/month',
			'500k auto-translation chars/month'
		];
	}

	const features = getFeatures();

	let price = $derived((() => {
		if (plan === 'personal') return yearly ? prices.personal : Math.round(prices.personal / 10);
		return prices[plan as keyof typeof prices] * (yearly ? 10 : 1);
	})());

	let period = $derived((() => {
		if (plan === 'personal') return yearly ? 'year' : 'month';
		return yearly ? 'year' : 'month';
	})());

	let personalMonthlyNote = $derived(plan === 'personal' && !yearly);
</script>

<div class="wrap hds-box" class:personal={plan === 'personal'}>
	<div class="name">{plan}</div>

	<div class="features">
		{#each features as feature}
			<div class="feature">
				<IconCheckCircle />
				<div class="feature-text">{feature}</div>
			</div>
		{/each}
	</div>

	<div class="price">
		{#if plan === 'personal'}
			<div class="annual-only">
				{personalMonthlyNote ? `* Annual billing only (€${prices.personal}/year)` : 'Annual billing only'}
			</div>
		{/if}
		<div class="price-display">
			<span class="price-amount">{currency}{price}</span><span class="price-period">/{period}{personalMonthlyNote ? '*' : ''}</span>
		</div>
	</div>

	<div class="button-wrap">
		<Button size="large" as="a" href="/console/billing">Choose Plan</Button>
	</div>
</div>

<style lang="scss">
	.wrap {
		flex: 1;
		display: flex;
		flex-direction: column;
	}
	.name {
		font-weight: 600;
		font-size: 25px;
		text-align: center;
		text-transform: capitalize;
		padding: 25px 20px;
		border-bottom: 1px solid var(--border);
	}
	.features {
		padding: 20px 30px;
		flex: 1;
	}
	.feature {
		display: flex;
		align-items: center;
		gap: 8px;
		padding: 6px 0;
		:global(svg) {
			color: var(--green);
		}
	}
	.feature-text {
		flex: 1;
	}
	.price {
		padding: 20px;
		text-align: center;
		background-color: #fafafa;
	}
	.annual-only {
		font-size: 12px;
		color: var(--text-light);
		margin-bottom: 4px;
	}
	.price-display {
		font-weight: 600;
		font-size: 28px;
	}
	.price-period {
		font-size: 18px;
		font-weight: normal;
		color: var(--text-light);
	}
	.button-wrap {
		padding: 25px 20px;
		display: flex;
		justify-content: center;
	}

	@media (max-width: 992px) {
		.wrap {
			flex-direction: column;
		}
	}
</style>
