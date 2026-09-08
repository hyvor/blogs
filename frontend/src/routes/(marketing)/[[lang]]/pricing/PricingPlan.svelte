<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconCheckLg from '@hyvor/icons/IconCheckLg';
	import { getMarketingI18n } from '../marketingLang';

	const I18n = getMarketingI18n();

	type PlanKey = 'personal' | 'starter' | 'growth' | 'premium' | 'enterprise';

	interface Props {
		plan: PlanKey;
		yearly: boolean;
		currency: string;
	}

	let { plan, yearly, currency }: Props = $props();

	type FeatureLine = { key: string; from?: PlanKey; value?: string | number; summary?: boolean };
	const PLAN_FEATURES: Record<PlanKey, FeatureLine[]> = {
		personal: [
			{ key: 'users', value: 1 },
			{ key: 'storage', value: 1 },
			{ key: 'blogs', value: 1 },
			{ key: 'customDomains' },
			{ key: 'customThemes' },
			{ key: 'multiLanguage' },
			{ key: 'allApis' },
			{ key: 'freeIntegrations' }
		],
		starter: [
			{ key: 'inherits', from: 'personal', summary: true },
			{ key: 'users', value: 5 },
			{ key: 'storage', value: 5 },
			{ key: 'aiAgent' },
			{ key: 'blogsMultiple' },
			{ key: 'noBranding' },
			{ key: 'seoAnalysis' }
		],
		growth: [
			{ key: 'inherits', from: 'starter', summary: true },
			{ key: 'users', value: 15 },
			{ key: 'storage', value: 150 },
			{ key: 'aiAgentUsage', value: 4 },
			{ key: 'linkAnalysis' }
		],
		premium: [
			{ key: 'inherits', from: 'growth', summary: true },
			{ key: 'users', value: 50 },
			{ key: 'storage', value: 500 },
			{ key: 'aiAgentUsage', value: 12 }
		],
		enterprise: [
			{ key: 'customLimits' },
			{ key: 'sso' },
			{ key: 'prioritySupport' },
			{ key: 'customContract' }
		]
	};

	const prices = {
		personal: 50, //per year
		starter: 12,
		growth: 40,
		premium: 125
	};

	const popular = plan === 'growth';
	const isEnterprise = plan === 'enterprise';

	const planName = $derived(I18n.t(`pricing.plans.${plan}` as never));
	const tagline = $derived(
		I18n.t(`pricing.plans.tagline${plan.charAt(0).toUpperCase()}${plan.slice(1)}` as never)
	);

	const items = $derived(
		PLAN_FEATURES[plan].map((f) => ({
			summary: !!f.summary,
			text: I18n.t(
				`pricing.plans.features.${f.key}` as never,
				f.from
					? { plan: I18n.t(`pricing.plans.${f.from}` as never) }
					: f.value !== undefined
						? { value: f.value }
						: {}
			)
		}))
	);

	let price = $derived(
		(() => {
			if (plan === 'personal') return yearly ? prices.personal : Math.round(prices.personal / 10);
			return prices[plan as keyof typeof prices] * (yearly ? 10 : 1);
		})()
	);

	let period = $derived(
		yearly ? I18n.t('pricing.plans.perYear') : I18n.t('pricing.plans.perMonth')
	);

	let personalMonthlyNote = $derived(plan === 'personal' && !yearly);

	const ctaLabel = $derived(
		isEnterprise ? I18n.t('pricing.plans.ctaContactSales') : I18n.t('pricing.plans.ctaChoosePlan')
	);
	const ctaHref = isEnterprise ? 'https://hyvor.com/enterprise' : '/console/billing';
</script>

<div class="wrap hds-box" class:popular>
	<div class="top">
		<div class="header">
			<div class="name">{planName}</div>
			{#if popular}
				<div class="popular-badge">{I18n.t('pricing.plans.popular')}</div>
			{/if}
		</div>

		{#if isEnterprise}
			<div class="price-row">
				<span class="price-amount">{I18n.t('pricing.plans.custom')}</span>
			</div>
		{:else}
			<div class="price-row">
				<span class="price-amount">{currency}{price}</span><span class="price-period"
					>{period}{personalMonthlyNote ? '*' : ''}</span
				>
			</div>
		{/if}

		<div class="annual-note">
			{personalMonthlyNote
				? I18n.t('pricing.plans.billedAnnually', { currency, price: prices.personal })
				: ' '}
		</div>

		<div class="tagline">{tagline}</div>

		<div class="cta-wrap">
			<Button
				as="a"
				href={ctaHref}
				target={isEnterprise ? '_blank' : undefined}
				rel={isEnterprise ? 'noopener' : undefined}
				size="large"
				block
				variant="fill"
				color={popular ? 'accent' : 'gray'}>{ctaLabel}</Button
			>
		</div>
	</div>

	<div class="bottom">
		<div class="features-list">
			{#each items as item}
				{#if item.summary}
					<div class="feature-item summary">
						<span>{item.text} +</span>
					</div>
				{:else}
					<div class="feature-item">
						<span class="check"><IconCheckLg size={10} /></span>
						<span>{item.text}</span>
					</div>
				{/if}
			{/each}
		</div>
	</div>
</div>

<style>
	.wrap {
		flex: 1;
		display: flex;
		flex-direction: column;
		overflow: hidden;
		transition: box-shadow 0.2s ease;
	}

	.wrap.popular {
		box-shadow: 0 12px 32px -14px color-mix(in srgb, var(--accent) 50%, transparent);
	}

	.top {
		padding: 26px 26px 24px;
		background: var(--hover);
		min-height: 240px;
		border-radius: var(--box-radius) var(--box-radius) 0 0;
	}

	.header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}

	.name {
		font-weight: 700;
		font-size: 19px;
		letter-spacing: -0.01em;
	}

	.popular-badge {
		font-size: 12px;
		font-weight: 700;
		color: var(--accent);
		background: var(--accent-light-mid);
		padding: 4px 11px;
		border-radius: 100px;
		white-space: nowrap;
	}

	.price-row {
		margin-top: 18px;
	}

	.price-amount {
		font-weight: 800;
		font-size: 42px;
		letter-spacing: -0.02em;
	}

	.price-period {
		font-size: 16px;
		font-weight: 500;
		color: var(--text-light);
		margin-left: 3px;
	}

	.annual-note {
		font-size: 12px;
		line-height: 1.4;
		min-height: 1.4em;
		color: var(--text-light);
		margin-top: 6px;
	}

	.tagline {
		font-size: 14px;
		color: var(--text-light);
		margin-top: 10px;
	}

	.cta-wrap {
		margin-top: 20px;
	}

	.bottom {
		flex: 1;
		padding: 22px 26px 26px;
		background: var(--box-background);
		border-radius: 0 0 var(--box-radius) var(--box-radius);
	}

	.feature-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 7px 0;
		font-size: 14px;
	}

	.feature-item.summary {
		font-weight: 700;
	}

	.check {
		width: 18px;
		height: 18px;
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: var(--accent);
		color: var(--accent-text);
	}
</style>
