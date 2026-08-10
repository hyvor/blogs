<script lang="ts">
	import IconLink45deg from '@hyvor/icons/IconLink45deg';
	import IconSearch from '@hyvor/icons/IconSearch';

	const scoreItems = [
		{ name: 'Performance', score: 100 },
		{ name: 'Accessibility', score: 100 },
		{ name: 'Best Practices', score: 100 },
		{ name: 'SEO', score: 100 }
	];

	// matches the semi-circle score meter in the real SEO analyzer
	const seoScore = 88;
	const seoGaugeLen = 220;
	const seoGaugeOffset = seoGaugeLen * (1 - seoScore / 100);

	let inView = $state(false);
	let animatedScores = $state(scoreItems.map(() => 0));
	let seoScoreAnimated = $state(0);

	function animateValue(target: number, onUpdate: (v: number) => void, delayMs = 0) {
		const duration = 1100;
		const startAt = performance.now() + delayMs;
		function step(now: number) {
			if (now < startAt) {
				requestAnimationFrame(step);
				return;
			}
			const progress = Math.min((now - startAt) / duration, 1);
			const eased = 1 - Math.pow(1 - progress, 3);
			onUpdate(Math.round(eased * target));
			if (progress < 1) requestAnimationFrame(step);
		}
		requestAnimationFrame(step);
	}

	function handleInView() {
		if (inView) return;
		inView = true;
		scoreItems.forEach((item, i) => animateValue(item.score, (v) => (animatedScores[i] = v), i * 120));
		animateValue(seoScore, (v) => (seoScoreAnimated = v), 200);
	}

	function onView(node: HTMLElement, callback: () => void) {
		const observer = new IntersectionObserver(
			(entries) => {
				if (entries[0]?.isIntersecting) {
					callback();
					observer.disconnect();
				}
			},
			{ threshold: 0.3 }
		);
		observer.observe(node);
		return {
			destroy() {
				observer.disconnect();
			}
		};
	}
</script>

<div class="seo-mockup" use:onView={handleInView}>
	<div class="scores-hero">
		{#each scoreItems as item, i}
			<div class="score-gauge" style="--delay: {i * 120}ms">
				<div class="gauge-ring">
					<svg class="gauge-svg" viewBox="0 0 120 120">
						<circle class="gauge-track" cx="60" cy="60" r="52" />
						<circle class="gauge-fill" class:animate={inView} cx="60" cy="60" r="52" />
					</svg>
					<div class="gauge-center">
						<span class="gauge-value">{animatedScores[i]}</span>
					</div>
				</div>
				<span class="gauge-name">{item.name}</span>
			</div>
		{/each}
	</div>

	<div class="analyzer-col">
		<div class="analyzer-card">
			<div class="seo-gauge">
				<svg class="seo-gauge-svg" viewBox="0 0 200 130">
					<defs>
						<linearGradient id="seoGaugeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
							<stop offset="0%" stop-color="#ef4444" />
							<stop offset="50%" stop-color="#f59e0b" />
							<stop offset="100%" stop-color="#22c55e" />
						</linearGradient>
					</defs>
					<path class="seo-gauge-track" d="M30,115 A70,70 0 0,1 170,115" />
					<path
						class="seo-gauge-fill"
						d="M30,115 A70,70 0 0,1 170,115"
						style="stroke-dashoffset: {inView ? seoGaugeOffset : seoGaugeLen}"
					/>
				</svg>
				<div class="seo-gauge-center">
					<span class="seo-gauge-value">{seoScoreAnimated}%</span>
				</div>
			</div>
		</div>

		<div class="analyzer-card">
			<div class="link-icons">
				<div class="link-icon-badge link-icon-badge-back">
					<IconLink45deg size={26} />
				</div>
				<div class="link-icon-badge link-icon-badge-front">
					<IconSearch size={20} />
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	.seo-mockup {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.scores-hero {
		display: flex;
		justify-content: space-between;
		gap: 12px;
		padding: 36px 28px;
		border-radius: 24px;
		background: radial-gradient(
			circle at 50% 0%,
			color-mix(in srgb, #22c55e 8%, var(--background)),
			var(--background)
		);
		box-shadow: 0 16px 48px color-mix(in srgb, var(--text) 8%, transparent);
	}

	.score-gauge {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 10px;
	}

	.gauge-ring {
		position: relative;
		width: 84px;
		height: 84px;
	}

	.gauge-svg {
		width: 100%;
		height: 100%;
		transform: rotate(-90deg);
	}

	.gauge-track {
		fill: none;
		stroke: color-mix(in srgb, var(--text) 8%, transparent);
		stroke-width: 8;
	}

	.gauge-fill {
		fill: none;
		stroke: #22c55e;
		stroke-width: 8;
		stroke-linecap: round;
		stroke-dasharray: 326.73;
		stroke-dashoffset: 326.73;
		transition: stroke-dashoffset 1.1s cubic-bezier(0.16, 1, 0.3, 1);
		transition-delay: var(--delay, 0ms);
	}

	.gauge-fill.animate {
		stroke-dashoffset: 0;
	}

	.gauge-center {
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.gauge-value {
		font-size: 20px;
		font-weight: 800;
		letter-spacing: -0.02em;
		color: var(--text);
	}

	.gauge-name {
		font-size: 11px;
		font-weight: 500;
		color: var(--text-light);
		text-align: center;
		max-width: 74px;
		line-height: 1.3;
	}

	.analyzer-col {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 12px;
	}

	.analyzer-card {
		display: flex;
		flex-direction: column;
		padding: 20px 18px;
		border-radius: 16px;
		background: var(--background);
		box-shadow: 0 4px 16px color-mix(in srgb, var(--text) 5%, transparent);
	}

	/* SEO score meter — just the arc + the number, no labels */
	.seo-gauge {
		position: relative;
		flex: 1;
		display: flex;
		align-items: flex-end;
		justify-content: center;
	}

	.seo-gauge-svg {
		width: 100%;
		max-width: 200px;
	}

	.seo-gauge-track {
		fill: none;
		stroke: color-mix(in srgb, var(--text) 8%, transparent);
		stroke-width: 22;
		stroke-linecap: round;
	}

	.seo-gauge-fill {
		fill: none;
		stroke: url(#seoGaugeGradient);
		stroke-width: 22;
		stroke-linecap: round;
		stroke-dasharray: 220;
		stroke-dashoffset: 220;
		transition: stroke-dashoffset 1.1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s;
	}

	.seo-gauge-center {
		position: absolute;
		left: 0;
		right: 0;
		bottom: 8px;
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 2px;
	}

	.seo-gauge-value {
		font-size: 26px;
		font-weight: 800;
		letter-spacing: -0.02em;
		color: var(--text);
	}

	/* Post Link Analyzer — link + magnifying glass, no text list */
	.link-icons {
		flex: 1;
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		min-height: 110px;
	}

	.link-icon-badge {
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 100px;
		border: 3px solid var(--background);
		color: #3b82f6;
		background: color-mix(in srgb, #3b82f6 14%, var(--background));
	}

	.link-icon-badge-back {
		width: 68px;
		height: 68px;
		transform: translate(-14px, -6px) rotate(-8deg);
	}

	.link-icon-badge-front {
		position: absolute;
		width: 48px;
		height: 48px;
		transform: translate(22px, 20px);
		color: #16a34a;
		background: color-mix(in srgb, #22c55e 16%, var(--background));
		box-shadow: 0 6px 16px color-mix(in srgb, var(--text) 10%, transparent);
	}

	@media (max-width: 900px) {
		.scores-hero {
			flex-wrap: wrap;
			justify-content: center;
			row-gap: 24px;
		}
	}

	@media (max-width: 480px) {
		.analyzer-col {
			grid-template-columns: 1fr;
		}
	}
</style>
