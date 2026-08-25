<script lang="ts">
	import PricingPlan from './PricingPlan.svelte';
	import IconChevronLeft from '@hyvor/icons/IconChevronLeft';
	import IconChevronRight from '@hyvor/icons/IconChevronRight';
	import { getMarketingI18n } from '../marketingLang';

	const I18n = getMarketingI18n();

	let currency = '€';
	let yearly = $state(false);

	let plansEl: HTMLDivElement | undefined = $state();
	let canScrollLeft = $state(false);
	let canScrollRight = $state(false);

	function updateScrollState() {
		if (!plansEl) return;
		canScrollLeft = plansEl.scrollLeft > 4;
		canScrollRight = plansEl.scrollLeft + plansEl.clientWidth < plansEl.scrollWidth - 4;
	}

	function scroll(dir: 1 | -1) {
		plansEl?.scrollBy({ left: dir * 320, behavior: 'smooth' });
	}

	$effect(() => {
		if (!plansEl) return;
		const el = plansEl;
		updateScrollState();
		el.addEventListener('scroll', updateScrollState, { passive: true });
		window.addEventListener('resize', updateScrollState);
		return () => {
			el.removeEventListener('scroll', updateScrollState);
			window.removeEventListener('resize', updateScrollState);
		};
	});
</script>

<section class="pricing-hero">
	<div class="header-wrap hds-container-max">
		<div class="header">
			<h1 class="title">{I18n.t('pricing.hero.title')}</h1>
			<p class="subtitle">{I18n.t('pricing.hero.subtitle')}</p>

			<div class="toggle-wrap">
				<div class="toggle" style="--active: {yearly ? 1 : 0}">
					<div class="toggle-thumb"></div>
					<button class="toggle-btn" class:active={!yearly} onclick={() => (yearly = false)}>
						{I18n.t('pricing.hero.monthly')}
					</button>
					<button class="toggle-btn" class:active={yearly} onclick={() => (yearly = true)}>
						{I18n.t('pricing.hero.annual')}
						<span class="save">{I18n.t('pricing.hero.annualSave')}</span>
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="plans-outer">
		<div class="plans-wrap">
			<div class="plans" bind:this={plansEl}>
				<div class="plan-slide"><PricingPlan plan="personal" {yearly} {currency} /></div>
				<div class="plan-slide"><PricingPlan plan="starter" {yearly} {currency} /></div>
				<div class="plan-slide"><PricingPlan plan="growth" {yearly} {currency} /></div>
				<div class="plan-slide"><PricingPlan plan="premium" {yearly} {currency} /></div>
				<div class="plan-slide"><PricingPlan plan="enterprise" {yearly} {currency} /></div>
			</div>

			{#if canScrollLeft}
				<button
					class="arrow left"
					onclick={() => scroll(-1)}
					aria-label={I18n.t('pricing.hero.scrollPrev')}
				>
					<IconChevronLeft size={16} />
				</button>
			{/if}

			{#if canScrollRight}
				<button
					class="arrow right"
					onclick={() => scroll(1)}
					aria-label={I18n.t('pricing.hero.scrollNext')}
				>
					<IconChevronRight size={16} />
				</button>
			{/if}
		</div>
	</div>
</section>

<style>
	.pricing-hero {
		position: relative;
		padding-bottom: 25px;
		background: linear-gradient(to bottom, var(--accent-light-mid), var(--background) 65%);
	}

	.header-wrap {
		position: relative;
	}

	.plans-outer {
		position: relative;
		/* distance from the true viewport edge to where the centered, max-1400px
		   container's own content starts — keeps the row's padding (and the
		   arrows) visually aligned with the title above, even though the row
		   itself runs full-bleed edge to edge */
		--container-inset: max(15px, calc((100vw - 1400px) / 2 + 15px));
	}

	.header {
		padding-top: 60px;
	}

	.title {
		font-family: var(--font-serif);
		font-weight: 700;
		font-size: clamp(32px, 4vw, 44px);
		letter-spacing: -0.01em;
		line-height: 1.15;
		margin: 0;
	}

	.subtitle {
		font-size: 18px;
		color: var(--text-light);
		margin: 10px 0 0;
	}

	.plans-wrap {
		position: relative;
		margin-top: 5px;
	}

	.plans {
		display: flex;
		align-items: stretch;
		gap: 20px;
		/* the row itself runs full viewport width (see .plans-outer, which is no
		   longer capped to the 1400px container), with padding standing in for
		   the container's inset — so both edges bleed the same way: a card
		   cut off on either side reads as "continues off-screen" rather than an
		   abrupt clip in the middle of the page */
		padding: 25px var(--container-inset);
		overflow-x: auto;
		scroll-snap-type: x proximity;
		/* without this, the snap engine auto-scrolls past the container's own
		   left padding on load (treating the border box, not the padding box,
		   as the snapport) — this keeps the first card's resting position at
		   scrollLeft 0, fully visible and aligned with the padding */
		scroll-padding: 0 var(--container-inset);
		scrollbar-width: none;
		-ms-overflow-style: none;
	}

	.plans::-webkit-scrollbar {
		display: none;
	}

	.plan-slide {
		display: flex;
		flex: 0 0 290px;
		width: 290px;
		scroll-snap-align: start;
	}

	.arrow {
		position: absolute;
		top: 50%;
		transform: translateY(-50%);
		display: flex;
		align-items: center;
		justify-content: center;
		width: 40px;
		height: 40px;
		border-radius: 50%;
		border: 1px solid var(--border);
		background: var(--box-background);
		box-shadow: var(--box-shadow);
		color: var(--text);
		cursor: pointer;
		z-index: 2;
		transition: background 0.15s ease;
	}

	.arrow:hover {
		background: var(--hover);
	}

	.arrow.left {
		left: calc(var(--container-inset) - 20px);
	}

	.arrow.right {
		right: calc(var(--container-inset) - 20px);
	}

	.toggle-wrap {
		display: flex;
		justify-content: flex-start;
		padding-top: 24px;
	}

	.toggle {
		position: relative;
		display: flex;
		/* wide enough for the longer of the two labels in any language (e.g.
		   "Annuel 2 mois offerts") to keep its padding on both sides — at a
		   fixed 50/50 split (see .toggle-thumb/.toggle-btn below), a narrower
		   width let a longer translation crowd right up against the pill edge */
		width: 330px;
		max-width: 100%;
		padding: 3px;
		border-radius: 100px;
		background: var(--hover);
		border: 1px solid var(--border);
	}

	.toggle-thumb {
		position: absolute;
		top: 3px;
		left: 3px;
		width: calc(50% - 3px);
		height: calc(100% - 6px);
		border-radius: 100px;
		background: var(--box-background);
		box-shadow: var(--box-shadow-light);
		transform: translateX(calc(var(--active) * 100%));
		transition: transform 0.3s cubic-bezier(0.65, 0, 0.35, 1);
	}

	.toggle-btn {
		position: relative;
		z-index: 1;
		flex: 1 1 0%;
		min-width: 0;
		border: none;
		background: transparent;
		padding: 7px 12px;
		border-radius: 100px;
		font-size: 13px;
		font-weight: 600;
		color: var(--text-light);
		cursor: pointer;
		white-space: nowrap;
		transition: color 0.2s ease;
	}

	.toggle-btn.active {
		color: var(--text);
	}

	.save {
		font-size: 12px;
		font-weight: 500;
		color: inherit;
		opacity: 0.75;
	}

	@media (max-width: 992px) {
		.plans {
			flex-direction: column;
			overflow-x: visible;
			padding: 25px 15px;
		}

		.plan-slide {
			flex: 1 1 auto;
			width: auto;
		}

		.arrow {
			display: none;
		}
	}

	@media (max-width: 600px) {
		/* keep in step with the wider .hds-container gutter set globally in
		   +layout.svelte at this same breakpoint */
		.plans {
			padding: 25px 20px;
		}
	}

	@media (max-width: 360px) {
		.toggle-btn {
			padding: 10px 8px;
			font-size: 13px;
		}
	}
</style>
