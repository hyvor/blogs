<script lang="ts">
	import { Tooltip } from '@hyvor/design/components';
	import IconInfoCircle from '@hyvor/icons/IconInfoCircle';
	import IconChevronLeft from '@hyvor/icons/IconChevronLeft';
	import IconChevronRight from '@hyvor/icons/IconChevronRight';
	import FeatureStatus from './FeatureStatus.svelte';
	import { getMarketingI18n } from '../marketingLang';

	const I18n = getMarketingI18n();

	type PlanKey = 'personal' | 'starter' | 'growth' | 'premium' | 'enterprise';

	interface Feature {
		name: string;
		personal: boolean | string;
		starter: boolean | string;
		growth: boolean | string;
		premium: boolean | string;
		enterprise: boolean | string;
		tooltip: string;
	}

	interface FeatureGroup {
		category: string;
		features: Feature[];
	}

	// drives both the desktop header row and — on mobile, where only one
	// plan's column shows at a time — the prev/next plan switcher
	const PLANS: { key: PlanKey; label: string; popular?: boolean }[] = $derived([
		{ key: 'personal', label: I18n.t('pricing.plans.personal') },
		{ key: 'starter', label: I18n.t('pricing.plans.starter') },
		{ key: 'growth', label: I18n.t('pricing.plans.growth'), popular: true },
		{ key: 'premium', label: I18n.t('pricing.plans.premium') },
		{ key: 'enterprise', label: I18n.t('pricing.plans.enterprise') }
	]);

	// mobile-only: which single plan's column is currently shown — starts on
	// Growth (the popular one) since that's the one most visitors want to see
	let mobilePlanIndex = $state(2);
	// mobilePlanIndex is always clamped to a valid PLANS index (see the
	// prev/next buttons below), so this index access is always in range
	const mobilePlan = $derived(PLANS[mobilePlanIndex]!);

	// per-cell VALUES (numbers, units like "tokens/m"/"credits/m", and the
	// "Custom" placeholder) are deliberately left untranslated — cleanly
	// i18n-ing them would mean splitting each into a {amount, unit} pair
	// instead of one free-form string, which is a bigger data model change
	const FEATURES: FeatureGroup[] = $derived([
		{
			category: I18n.t('pricing.compare.categories.basic'),
			features: [
				{
					name: I18n.t('pricing.compare.features.blogs.name'),
					personal: '1',
					starter: '3',
					growth: '10',
					premium: '20',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.blogs.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.users.name'),
					personal: '1',
					starter: '5',
					growth: '15',
					premium: '50',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.users.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.mediaStorage.name'),
					personal: '1GB',
					starter: '5GB',
					growth: '150GB',
					premium: '500GB',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.mediaStorage.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.customThemes.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.customThemes.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.customDomain.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.customDomain.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.multiLanguage.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.multiLanguage.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.noBranding.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.noBranding.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.seoAnalysis.name'),
					personal: false,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.seoAnalysis.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.linkAnalysis.name'),
					personal: false,
					starter: false,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.linkAnalysis.tooltip')
				}
			]
		},
		{
			category: I18n.t('pricing.compare.categories.ai'),
			features: [
				{
					name: I18n.t('pricing.compare.features.gptWriting.name'),
					personal: false,
					starter: false,
					growth: '100k tokens/m',
					premium: '1m tokens/m',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.gptWriting.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.autoTranslations.name'),
					personal: false,
					starter: false,
					growth: '100k chars/m',
					premium: '500k chars/m',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.autoTranslations.tooltip')
				}
			]
		},
		{
			category: I18n.t('pricing.compare.categories.developer'),
			features: [
				{
					name: I18n.t('pricing.compare.features.dataApi.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.dataApi.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.consoleApi.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.consoleApi.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.deliveryApi.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.deliveryApi.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.webhooks.name'),
					personal: true,
					starter: true,
					growth: true,
					premium: true,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.webhooks.tooltip')
				}
			]
		},
		{
			category: I18n.t('pricing.compare.categories.integrations'),
			features: [
				{
					name: I18n.t('pricing.compare.features.hyvorTalk.name'),
					personal: '10k credits/m',
					starter: '25k credits/m',
					growth: '100k credits/m',
					premium: '250k credits/m',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.hyvorTalk.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.hyvorPost.name'),
					personal: '5k emails/m',
					starter: '15k emails/m',
					growth: '50k emails/m',
					premium: '150k emails/m',
					enterprise: 'Custom',
					tooltip: I18n.t('pricing.compare.features.hyvorPost.tooltip')
				}
			]
		},
		{
			category: I18n.t('pricing.compare.categories.enterprise'),
			features: [
				{
					name: I18n.t('pricing.compare.features.sso.name'),
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.sso.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.compliance.name'),
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.compliance.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.prioritySupport.name'),
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.prioritySupport.tooltip')
				},
				{
					name: I18n.t('pricing.compare.features.customContract.name'),
					personal: false,
					starter: false,
					growth: false,
					premium: false,
					enterprise: true,
					tooltip: I18n.t('pricing.compare.features.customContract.tooltip')
				}
			]
		}
	]);
</script>

<div class="title-wrap hds-container">
	<div class="eyebrow">{I18n.t('pricing.compare.eyebrow')}</div>
	<h2>{I18n.t('pricing.compare.title')}</h2>
	<p class="subtitle">{I18n.t('pricing.compare.subtitle')}</p>
</div>

<div class="table-outer hds-container-max">
	<!-- mobile only: swap the header row for a prev/next switcher, since only
	     one plan's column shows at a time below (see .col.mobile-hidden) -->
	<div class="mobile-plan-switcher">
		<button
			class="switch-arrow"
			onclick={() => (mobilePlanIndex = Math.max(0, mobilePlanIndex - 1))}
			disabled={mobilePlanIndex === 0}
			aria-label={I18n.t('pricing.compare.prevPlan')}
		>
			<IconChevronLeft size={16} />
		</button>
		<span class="switch-label">
			{mobilePlan.label}
			{#if mobilePlan.popular}
				<span class="popular-tag">{I18n.t('pricing.plans.mostPopular')}</span>
			{/if}
		</span>
		<button
			class="switch-arrow"
			onclick={() => (mobilePlanIndex = Math.min(PLANS.length - 1, mobilePlanIndex + 1))}
			disabled={mobilePlanIndex === PLANS.length - 1}
			aria-label={I18n.t('pricing.compare.nextPlan')}
		>
			<IconChevronRight size={16} />
		</button>
	</div>

	<div class="table-wrap hds-box">
		<div class="plan-names">
			<div class="col-name"></div>
			{#each PLANS as plan, i}
				<div class="col" class:popular={plan.popular} class:mobile-hidden={i !== mobilePlanIndex}>
					{#if plan.popular}
						<span>{plan.label}</span><span class="popular-tag"
							>{I18n.t('pricing.plans.mostPopular')}</span
						>
					{:else}
						{plan.label}
					{/if}
				</div>
			{/each}
		</div>

		{#each FEATURES as { category, features }}
			<div class="category-block">
				<div class="category-name">
					<div class="col-name">{category}</div>
					{#each PLANS as plan, i}
						<div
							class="col"
							class:popular={plan.popular}
							class:mobile-hidden={i !== mobilePlanIndex}
						></div>
					{/each}
				</div>
				{#each features as feature}
					<div class="feature-row">
						<div class="col-name">
							{feature.name}&nbsp;
							<Tooltip text={feature.tooltip} position="right">
								<IconInfoCircle />
							</Tooltip>
						</div>
						{#each PLANS as plan, i}
							<div
								class="col"
								class:popular={plan.popular}
								class:mobile-hidden={i !== mobilePlanIndex}
							>
								<FeatureStatus status={feature[plan.key]} />
							</div>
						{/each}
					</div>
				{/each}
			</div>
		{/each}
	</div>
</div>

<style lang="scss">
	.title-wrap {
		padding-top: 100px;
		text-align: center;
	}

	.eyebrow {
		display: inline-block;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		color: var(--accent);
		margin-bottom: 14px;
	}

	h2 {
		font-family: var(--font-serif);
		font-size: 32px;
		font-weight: 700;
		letter-spacing: -0.01em;
		margin: 0 0 10px;
	}

	.subtitle {
		font-size: 16px;
		color: var(--text-light);
		margin: 0;
	}

	.table-outer {
		margin-top: 44px;
	}

	.table-wrap {
		width: 1400px;
		max-width: 100%;
		margin: auto;
		overflow-x: auto;
		/* NOTE: overflow-x:auto here forces overflow-y's *computed* value to
		   auto too, no matter what overflow-y is set to (that's spec — one
		   axis can't be truly 'visible' while the other is scrolling), so a
		   real y-scrollbar appears the instant content exceeds this box. The
		   popular column's top/bottom overshoot (see .col.popular below) must
		   therefore fit entirely inside this padding, not rely on escaping it */
		padding: 30px 30px 30px;
	}

	.col-name {
		flex: 1.4;
		min-width: 190px;
	}

	.col {
		flex: 1;
		min-width: 110px;
		text-align: center;
	}

	.plan-names {
		display: flex;
		align-items: center;
		font-size: 16px;
		font-weight: 700;
		background-color: var(--box-background);

		.col-name {
			visibility: hidden;
		}

		.col {
			position: relative;
		}

		.col.popular {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			color: var(--accent);
			background: var(--accent-light-mid);
			border-radius: 12px 12px 0 0;
			border-top: 2px solid var(--accent);
			/* no horizontal padding — with flex-basis:0%, a flex item's own
			   padding/border adds directly on top of its share of the
			   distributed space, so giving only this cell extra left/right
			   padding (the other .col cells have none) is what was making the
			   header pill wider than the highlighted column beneath it */
			padding: 18px 0 8px;
			/* pulls the box up 20px past the table's top edge — padding-top
			   grew to match so the "Growth" text itself stays put */
			margin: -20px 0 0;
		}
	}

	.popular-tag {
		display: inline-flex;
		font-size: 10px;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: var(--accent-text);
		background: var(--accent);
		border-radius: 100px;
		padding: 2px 8px;
		white-space: nowrap;
	}

	.category-name {
		display: flex;
		align-items: center;

		.col-name {
			font-weight: 700;
			font-size: 14px;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			color: var(--text-light);
			padding-top: 26px;
			padding-bottom: 8px;
		}

		/* the empty popular-column cell has no content of its own to size it —
		   stretch it to the row's full height so the highlight band doesn't
		   collapse to nothing at each category header */
		.col.popular {
			align-self: stretch;
		}
	}

	.feature-row {
		display: flex;
		align-items: stretch;

		.col-name {
			cursor: default;
			display: inline-flex;
			align-items: center;
			gap: 3px;
			padding: 11px 0;
		}

		.col {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 11px 0;
		}

		&:hover {
			background-color: var(--hover);
		}
	}

	.col.popular {
		background-color: var(--accent-light-mid);
		/* every popular cell contributes the same left/right edge, at the same
		   x-position, stacked with zero gap between rows — so these read as
		   one continuous outline running down both sides of the column,
		   rather than needing a single unified element */
		border-left: 2px solid var(--accent);
		border-right: 2px solid var(--accent);
	}

	/* the highlight's bottom cap — mirrors the header pill's top cap (rounded
	   corners + padding/margin overshoot) so the whole Growth column reads as
	   one marker stroke that runs a bit past the table on both ends, not a
	   plain rectangle flush with it */
	.category-block:last-child .feature-row:last-child .col.popular {
		border-radius: 0 0 12px 12px;
		border-bottom: 2px solid var(--accent);
		padding-bottom: 29px;
		/* pulls the bottom edge 20px past the table's own bottom */
		margin-bottom: -20px;
	}

	@media (max-width: 640px) {
		.table-wrap {
			/* bottom must stay >= the popular column's bottom overshoot (20px,
			   see .category-block:last-child above) or it clips again */
			padding: 24px 20px 30px;
		}
	}

	.mobile-plan-switcher {
		display: none;
	}

	@media (max-width: 768px) {
		.mobile-plan-switcher {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 14px;
			margin-bottom: 16px;
		}

		// only within this breakpoint — desktop always shows all 5 columns.
		// !important because ".plan-names .col.popular { display: flex }"
		// (3 classes) outranks any 2-class ".col.mobile-hidden" selector, so
		// without it the Growth column stays visible even when not selected
		.col.mobile-hidden {
			display: none !important;
		}

		.switch-arrow {
			flex-shrink: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			width: 36px;
			height: 36px;
			border-radius: 50%;
			border: 1px solid var(--border);
			background: var(--box-background);
			color: var(--text);
			cursor: pointer;
			transition: background 0.15s ease;
		}

		.switch-arrow:hover:not(:disabled) {
			background: var(--hover);
		}

		.switch-arrow:disabled {
			opacity: 0.35;
			cursor: not-allowed;
		}

		.switch-label {
			display: flex;
			align-items: center;
			gap: 8px;
			min-width: 130px;
			justify-content: center;
			font-size: 17px;
			font-weight: 700;
		}

		// the header row's own plan name is redundant with the switcher above
		// it now, and every cell in it is empty except whichever one is the
		// selected plan (already labeled by the switcher)
		.plan-names {
			display: none;
		}

		.col-name {
			flex: 1.6;
			min-width: 0;
		}

		.col {
			min-width: 0;
		}

		// the "most popular" frame (tinted background + accent border, plus
		// the rounded overshoot on the last row) only makes sense when the
		// Growth column sits next to its siblings for comparison — with one
		// plan shown at a time on mobile, it reads as a stray box instead
		.col.popular {
			background-color: transparent;
			border-left: none;
			border-right: none;
		}

		.category-block:last-child .feature-row:last-child .col.popular {
			border-bottom: none;
			border-radius: 0;
			padding-bottom: 11px;
			margin-bottom: 0;
		}
	}
</style>
