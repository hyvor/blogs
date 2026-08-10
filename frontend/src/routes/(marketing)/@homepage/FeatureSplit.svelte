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
		visual: Snippet;
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
		visual,
		after,
		left
	}: Props = $props();

	let inView = $state(false);

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
					<ul class="bullets">
						{#each bullets as b}
							<li><IconCheckCircleFill size={14} />{b}</li>
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
			{@render visual()}
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
