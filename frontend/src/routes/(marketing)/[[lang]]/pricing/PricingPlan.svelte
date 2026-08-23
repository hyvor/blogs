<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconCheckLg from '@hyvor/icons/IconCheckLg';

	interface Props {
		plan: 'personal' | 'starter' | 'growth' | 'premium' | 'enterprise';
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

	const meta = {
		personal: { tagline: 'For solo bloggers' },
		starter: { tagline: 'For small teams' },
		growth: { tagline: 'For growing publications' },
		premium: { tagline: 'For large organizations' },
		enterprise: { tagline: 'For custom, large-scale needs' }
	};

	const popular = plan === 'growth';
	const isEnterprise = plan === 'enterprise';

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
			return ['All Personal features', '3 blogs', '5 users', '5GB media storage', 'SEO Analysis'];
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
		if (plan === 'premium') {
			return [
				'All Growth features',
				'20 blogs',
				'50 users',
				'500GB media storage',
				'1m AI tokens/month',
				'500k auto-translation chars/month'
			];
		}
		return [
			'All Premium features',
			'SSO & SAML',
			'GDPR, CCPA & ISO compliant',
			'Priority support',
			'Custom contract & SLA'
		];
	}

	const items = getFeatures();

	let price = $derived(
		(() => {
			if (plan === 'personal') return yearly ? prices.personal : Math.round(prices.personal / 10);
			return prices[plan as keyof typeof prices] * (yearly ? 10 : 1);
		})()
	);

	let period = $derived(yearly ? 'year' : 'month');

	let personalMonthlyNote = $derived(plan === 'personal' && !yearly);

	const ctaLabel = isEnterprise ? 'Contact Sales' : 'Choose Plan';
	const ctaHref = isEnterprise ? 'https://hyvor.com/enterprise' : '/console/billing';
</script>

<div class="wrap hds-box" class:popular>
	<div class="top">
		<div class="header">
			<div class="name">{plan}</div>
			{#if popular}
				<div class="popular-badge">Popular</div>
			{/if}
		</div>

		{#if isEnterprise}
			<div class="price-row">
				<span class="price-amount">Custom</span>
			</div>
		{:else}
			<div class="price-row">
				<span class="price-amount">{currency}{price}</span><span class="price-period"
					>/{period}{personalMonthlyNote ? '*' : ''}</span
				>
			</div>
		{/if}

		<div class="annual-note">
			{personalMonthlyNote ? `*Billed annually at ${currency}${prices.personal}/year` : ' '}
		</div>

		<div class="tagline">{meta[plan].tagline}</div>

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
		<div class="features-label">Features</div>
		<div class="features-list">
			{#each items as item}
				<div class="feature-item">
					<span class="check"><IconCheckLg size={10} /></span>
					<span>{item}</span>
				</div>
			{/each}
		</div>
	</div>
</div>

<style lang="scss">
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
		text-transform: capitalize;
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

	.features-label {
		font-size: 12px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.06em;
		color: var(--text-light);
	}

	.features-list {
		margin-top: 14px;
	}

	.feature-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 7px 0;
		font-size: 14px;
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
