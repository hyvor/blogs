<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import type { Snippet } from 'svelte';

	type ButtonConfig = { href: string; label: string; external?: boolean };

	interface Props {
		eyebrow: string;
		title: string | Snippet;
		description: string;
		bullets?: string[];
		button?: ButtonConfig | ButtonConfig[] | null;
		flip?: boolean;
		altBg?: boolean;
		// lets the visual bleed under the text column (e.g. negative margin on the visual)
		// while keeping the text readable on top of it
		overlap?: boolean;
		// when true, bullets become clickable and the active index is passed to `visual`,
		// so the visual can switch state to match whichever bullet is selected
		interactiveBullets?: boolean;
		visual: Snippet<[activeBullet: number]>;
		after?: Snippet;
		left?: Snippet;
	}

	let {
		eyebrow,
		title,
		description,
		bullets = [],
		button = null,
		flip = false,
		altBg = false,
		overlap = false,
		interactiveBullets = false,
		visual,
		after,
		left
	}: Props = $props();

	let inView = $state(false);
	let activeBullet = $state(0);

	// how long the active bullet's progress bar takes to fill before auto-advancing
	const AUTO_ADVANCE_MS = 5000;

	$effect(() => {
		if (!interactiveBullets || !inView || bullets.length <= 1) return;
		// read explicitly so the effect re-runs (and reschedules) every time
		// activeBullet changes — from this timer firing, or from a manual click
		activeBullet;
		const count = bullets.length;
		const timer = setTimeout(() => {
			activeBullet = (activeBullet + 1) % count;
		}, AUTO_ADVANCE_MS);
		return () => clearTimeout(timer);
	});

	function onView(node: HTMLElement, callback: () => void) {
		const observer = new IntersectionObserver(
			(entries) => {
				if (entries[0]?.isIntersecting) {
					callback();
					observer.disconnect();
				}
			},
			{ threshold: 0.25 }
		);
		observer.observe(node);
		return {
			destroy() {
				observer.disconnect();
			}
		};
	}
</script>

<section class="feature-section" class:alt-bg={altBg}>
	<div class="hds-container split" class:flip class:overlap>
		<div class="text-col" class:in-view={inView} use:onView={() => (inView = true)}>
			{#if left}
				{@render left()}
			{:else}
				<span class="eyebrow">{eyebrow}</span>
				<h2>
					{#if typeof title === 'string'}
						{@html title}
					{:else}
						{@render title()}
					{/if}
				</h2>
				<p>{description}</p>

				{#if bullets.length}
					<ul class="bullets" class:interactive={interactiveBullets}>
						{#each bullets as b, i}
							<li>
								{#if interactiveBullets}
									<button
										type="button"
										class="bullet-btn"
										class:active={activeBullet === i}
										style="animation-duration: {AUTO_ADVANCE_MS}ms"
										onclick={() => (activeBullet = i)}
									>
										<IconCheckCircleFill size={14} />{b}
									</button>
								{:else}
									<IconCheckCircleFill size={14} />{b}
								{/if}
							</li>
						{/each}
					</ul>
				{/if}

				{#if button}
					{@const buttons = Array.isArray(button) ? button : [button]}
					<div class="buttons">
						{#each buttons as b}
							<Button
								as="a"
								href={b.href}
								target={b.external ? '_blank' : undefined}
								rel={b.external ? 'noopener' : undefined}
								variant="outline"
								size="small"
							>
								{b.label}
								{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
							</Button>
						{/each}
					</div>
				{/if}
			{/if}
		</div>

		<div class="visual-col">
			{@render visual(activeBullet)}
		</div>
	</div>

	{#if after}
		<div class="hds-container-max">
			{@render after()}
		</div>
	{/if}
</section>

<style>
	.feature-section {
		padding: 100px 0;
		overflow-x: hidden;
	}

	.alt-bg {
		background: color-mix(in srgb, var(--accent) 10%, var(--background));
	}

	.split {
		display: flex;
		align-items: center;
		gap: 72px;
	}

	.split.flip {
		flex-direction: row-reverse;
	}

	.text-col,
	.visual-col {
		flex: 1;
		min-width: 0;
	}

	.text-col {
		opacity: 0;
		transform: translateY(18px);
		transition:
			opacity 0.7s cubic-bezier(0.22, 1, 0.36, 1),
			transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
	}

	.text-col.in-view {
		opacity: 1;
		transform: none;
	}

	/* flex items honor z-index for paint order without needing `position` —
	   important here, since giving .visual-col its own stacking context would
	   trap the theme dropdown's position:fixed popup beneath the text column */
	.split.overlap .text-col {
		z-index: 2;
	}

	.eyebrow {
		display: inline-block;
		font-size: 12px;
		font-weight: 700;
		letter-spacing: 0.1em;
		text-transform: uppercase;
		color: var(--accent);
		margin-bottom: 16px;
	}

	h2 {
		font-size: clamp(26px, 3.5vw, 38px);
		font-weight: 800;
		letter-spacing: -0.02em;
		line-height: 1.15;
		margin: 0 0 16px;
	}

	p {
		font-size: 16px;
		line-height: 1.7;
		color: #414141;
		margin: 0 0 28px;
		max-width: 440px;
	}

	.bullets {
		list-style: none;
		margin: 0 0 32px;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.bullets li {
		display: flex;
		align-items: center;
		gap: 10px;
		font-size: 1rem;
	}

	.bullets li :global(svg) {
		color: var(--accent);
		flex-shrink: 0;
	}

	.bullet-btn {
		position: relative;
		display: flex;
		align-items: center;
		gap: 10px;
		width: 100%;
		padding: 8px 10px;
		border: none;
		border-radius: 20px;
		background-color: transparent;
		background-image: linear-gradient(
			color-mix(in srgb, var(--accent) 14%, transparent),
			color-mix(in srgb, var(--accent) 14%, transparent)
		);
		background-repeat: no-repeat;
		background-position: left center;
		background-size: 0% 100%;
		color: var(--text-light);
		font: inherit;
		font-size: 1rem;
		text-align: left;
		cursor: pointer;
		transition:
			background-color 0.2s,
			color 0.2s;
	}

	.bullet-btn:hover {
		background-color: color-mix(in srgb, var(--text) 4%, transparent);
	}

	.bullet-btn.active {
		color: var(--text);
		font-weight: 600;
		animation-name: bullet-fill;
		animation-timing-function: linear;
		animation-fill-mode: forwards;
	}

	@keyframes bullet-fill {
		from {
			background-size: 0% 100%;
		}
		to {
			background-size: 100% 100%;
		}
	}

	.bullets.interactive li :global(svg) {
		color: var(--text-light);
	}

	.bullets.interactive li:has(.bullet-btn.active) :global(svg) {
		color: var(--accent);
	}

	.buttons {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
	}

	@media (max-width: 900px) {
		.split,
		.split.flip {
			flex-direction: column;
			align-items: stretch;
			gap: 48px;
		}

		.text-col {
			width: 100%;
			text-align: center;
		}

		.visual-col {
			width: 100%;
		}

		p {
			max-width: 100%;
		}

		.bullets {
			align-items: center;
		}

		.buttons {
			justify-content: center;
		}
	}
</style>
