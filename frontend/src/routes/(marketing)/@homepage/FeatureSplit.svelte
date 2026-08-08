<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';
	import IconCheckCircleFill from '@hyvor/icons/IconCheckCircleFill';
	import type { Snippet } from 'svelte';

	interface Props {
		eyebrow: string;
		title: string;
		description: string;
		bullets?: string[];
		button?: { href: string; label: string } | null;
		flip?: boolean;
		altBg?: boolean;
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
		visual,
		after,
		left
	}: Props = $props();
</script>

<section class="feature-section" class:alt-bg={altBg}>
	<div class="hds-container split" class:flip>
		<div class="text-col">
			{#if left}
				{@render left()}
			{:else}
				<span class="eyebrow">{eyebrow}</span>
				<h2>{title}</h2>
				<p>{description}</p>

				{#if bullets.length}
					<ul class="bullets">
						{#each bullets as b}
							<li><IconCheckCircleFill size={14} />{b}</li>
						{/each}
					</ul>
				{/if}

				{#if button}
					<Button as="a" href={button.href} variant="outline" size="small">
						{button.label}
						{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
					</Button>
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
	}

	.alt-bg {
		background: color-mix(in srgb, var(--accent) 3%, var(--background));
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
		color: var(--text-light);
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
	}
</style>
