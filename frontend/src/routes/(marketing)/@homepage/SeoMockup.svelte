<script lang="ts">
	const scoreItems = [
		{ name: 'Performance', score: 100 },
		{ name: 'Accessibility', score: 100 },
		{ name: 'Best Practices', score: 100 },
		{ name: 'SEO', score: 100 }
	];

	const analyzerSummary = [
		{
			color: '#22c55e',
			title: 'SEO Analyzer',
			note: 'Real-time feedback',
			variant: 'checklist' as const,
			checklist: ['Primary keyword in title', 'Meta description set', 'Content length (400+ words)']
		},
		{
			color: '#3b82f6',
			title: 'Post Link Analyzer',
			note: 'Per-post analysis',
			variant: 'stats' as const,
			stats: [
				['Internal links', '12'],
				['Broken links', '0']
			]
		},
		{
			color: '#f59e0b',
			title: 'Full-blog Link Analyzer',
			note: '19 OK · 1 Redirect',
			variant: 'links' as const,
			links: [
				{ name: 'Hyvor Blogs', status: 'ok' },
				{ name: 'Multi-language', status: 'ok' },
				{ name: 'best blogging platform', status: 'redirect' }
			]
		}
	];

	let inView = $state(false);
	let animatedScores = $state(scoreItems.map(() => 0));

	function animateScore(index: number, target: number) {
		const duration = 1100;
		const start = performance.now();
		function step(now: number) {
			const progress = Math.min((now - start) / duration, 1);
			const eased = 1 - Math.pow(1 - progress, 3);
			animatedScores[index] = Math.round(eased * target);
			if (progress < 1) requestAnimationFrame(step);
		}
		requestAnimationFrame(step);
	}

	function handleInView() {
		if (inView) return;
		inView = true;
		scoreItems.forEach((item, i) => animateScore(i, item.score));
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
		{#each analyzerSummary as a}
			<div class="analyzer-card">
				<div class="ac-head">
					<span class="ac-dot" style="background:{a.color}"></span>
					<span class="ac-title">{a.title}</span>
					<span class="ac-note">{a.note}</span>
				</div>

				{#if a.variant === 'checklist'}
					<div class="ac-checklist">
						{#each a.checklist as label}
							<div class="ac-check-row">
								<span class="ac-pill ac-pill-pass">✓</span>
								<span class="ac-check-label">{label}</span>
							</div>
						{/each}
					</div>
				{:else if a.variant === 'links'}
					<div class="ac-links">
						{#each a.links as l}
							<div class="ac-link-row">
								<span class="ac-link-name">{l.name}</span>
								<span
									class="ac-pill"
									class:ac-pill-pass={l.status === 'ok'}
									class:ac-pill-info={l.status === 'redirect'}
								>
									{l.status === 'ok' ? 'OK' : 'Redirect'}
								</span>
							</div>
						{/each}
					</div>
				{:else}
					<div class="ac-stats">
						{#each a.stats as [label, val]}
							<div class="ac-stat">
								<span class="ac-stat-val">{val}</span>
								<span class="ac-stat-label">{label}</span>
							</div>
						{/each}
					</div>
				{/if}
			</div>
		{/each}
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
		border: 1px solid var(--border);
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
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.analyzer-card {
		padding: 16px 18px;
		border-radius: 16px;
		border: 1px solid var(--border);
		background: var(--background);
		box-shadow: 0 4px 16px color-mix(in srgb, var(--text) 5%, transparent);
	}

	.ac-head {
		display: flex;
		align-items: center;
		gap: 9px;
		margin-bottom: 14px;
	}

	.ac-dot {
		width: 9px;
		height: 9px;
		border-radius: 50%;
		flex-shrink: 0;
	}

	.ac-title {
		font-size: 14px;
		font-weight: 600;
		color: var(--text);
	}

	.ac-note {
		font-size: 12px;
		color: var(--text-light);
		margin-left: auto;
	}

	.ac-stats {
		display: flex;
		gap: 28px;
		padding-top: 14px;
		border-top: 1px solid color-mix(in srgb, var(--border) 70%, transparent);
	}

	.ac-stat {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	.ac-stat-val {
		font-size: 20px;
		font-weight: 800;
		letter-spacing: -0.02em;
		color: var(--text);
	}

	.ac-stat-label {
		font-size: 11px;
		color: var(--text-light);
	}

	.ac-checklist,
	.ac-links {
		display: flex;
		flex-direction: column;
		gap: 9px;
		padding-top: 14px;
		border-top: 1px solid color-mix(in srgb, var(--border) 70%, transparent);
	}

	.ac-check-row {
		display: flex;
		align-items: center;
		gap: 9px;
	}

	.ac-check-label {
		font-size: 13px;
		color: var(--text);
	}

	.ac-link-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 10px;
	}

	.ac-link-name {
		font-size: 13px;
		color: var(--text);
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.ac-pill {
		flex-shrink: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		min-width: 20px;
		height: 20px;
		padding: 0 8px;
		border-radius: 100px;
		font-size: 11px;
		font-weight: 700;
		background: color-mix(in srgb, var(--text) 8%, transparent);
		color: var(--text-light);
	}

	.ac-pill.ac-pill-pass {
		background: color-mix(in srgb, #22c55e 15%, transparent);
		color: #16a34a;
	}

	.ac-pill.ac-pill-info {
		background: color-mix(in srgb, #3b82f6 15%, transparent);
		color: #3b82f6;
	}

	@media (max-width: 900px) {
		.scores-hero {
			flex-wrap: wrap;
			justify-content: center;
			row-gap: 24px;
		}
	}
</style>
