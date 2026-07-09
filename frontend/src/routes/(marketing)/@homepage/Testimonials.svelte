<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';

	const testimonials = [
		{
			quote:
				'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.',
			name: 'Lionel S.',
			role: 'Blogger'
		},
		{
			quote:
				'The platform offers a seamless and user-friendly experience for both bloggers and readers. The customisation options are extensive, allowing us to create a unique and visually appealing blog.',
			name: 'Manoj P.',
			role: 'Senior Application Engineer'
		}
	];

	const AUTOPLAY_MS = 6000;

	let active = $state(0);
	let paused = $state(false);

	function goTo(i: number) {
		active = i;
	}

	$effect(() => {
		const id = setInterval(() => {
			if (!paused) {
				active = (active + 1) % testimonials.length;
			}
		}, AUTOPLAY_MS);
		return () => clearInterval(id);
	});
</script>

<section class="testimonials">
	<div class="hds-container inner">
		<p class="label">What our customers say</p>

		<div
			class="carousel-wrap"
			role="region"
			aria-label="Customer testimonials"
			onmouseenter={() => (paused = true)}
			onmouseleave={() => (paused = false)}
		>
			<div class="carousel">
				<div class="track" style="transform: translateX(-{active * 100}%)">
					{#each testimonials as t}
						<figure class="card">
							<div class="stars" aria-label="5 out of 5 stars">
								{#each [1, 2, 3, 4, 5] as _}
									<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" fill="#f59e0b">
										<path
											d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"
										/>
									</svg>
								{/each}
							</div>
							<blockquote>"{t.quote}"</blockquote>
							<figcaption>
								<strong>{t.name}</strong>
								<span>{t.role}</span>
							</figcaption>
						</figure>
					{/each}
				</div>
			</div>

			<div class="dots">
				{#each testimonials as _, i}
					<button
						class="dot"
						class:active={i === active}
						aria-label="Show testimonial {i + 1} of {testimonials.length}"
						aria-current={i === active}
						onclick={() => goTo(i)}
					></button>
				{/each}
			</div>
		</div>

		<div class="g2-link">
			<Button
				as="a"
				href="https://www.g2.com/products/hyvor-blogs/reviews"
				target="_blank"
				variant="outline"
				color="input"
				size="small"
			>
				Read more reviews on G2
				{#snippet end()}<IconBoxArrowUpRight size={11} />{/snippet}
			</Button>
		</div>
	</div>
</section>

<style>
	.testimonials {
		background: #0f0f11;
		padding: 96px 0;
	}

	.inner {
		text-align: center;
	}

	.label {
		font-size: 16px;
		font-weight: 600;
		letter-spacing: 0.07em;
		text-transform: uppercase;
		color: rgba(255, 255, 255, 0.35);
		margin: 0 0 48px;
	}

	.carousel-wrap {
		max-width: 640px;
		margin: 0 auto 40px;
	}

	.carousel {
		overflow: hidden;
		border-radius: 20px;
	}

	.track {
		display: flex;
		transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
	}

	.card {
		flex: 0 0 100%;
		width: 100%;
		box-sizing: border-box;
		border-radius: 20px;
		border: 1px solid rgba(255, 255, 255, 0.08);
		background: rgba(255, 255, 255, 0.04);
		padding: 32px;
		margin: 0;
		display: flex;
		flex-direction: column;
		gap: 20px;
		text-align: left;
	}

	.dots {
		display: flex;
		justify-content: center;
		gap: 8px;
		margin-top: 20px;
	}

	.dot {
		width: 8px;
		height: 8px;
		padding: 0;
		border: none;
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.2);
		cursor: pointer;
		transition:
			background 0.2s,
			transform 0.2s;
	}

	.dot.active {
		background: #fff;
		transform: scale(1.3);
	}

	@media (prefers-reduced-motion: reduce) {
		.track {
			transition: none;
		}
	}

	.stars {
		display: flex;
		gap: 3px;
	}

	blockquote {
		font-size: 16px;
		line-height: 1.7;
		color: rgba(255, 255, 255, 0.8);
		margin: 0;
		flex: 1;
	}

	figcaption {
		display: flex;
		flex-direction: column;
		gap: 2px;
	}

	figcaption strong {
		font-size: 14px;
		font-weight: 600;
		color: #fff;
	}

	figcaption span {
		font-size: 13px;
		color: rgba(255, 255, 255, 0.4);
	}

	.g2-link :global(.button) {
		border-color: rgba(255, 255, 255, 0.15) !important;
		color: rgba(255, 255, 255, 0.6) !important;
		background: transparent !important;
	}

	.g2-link :global(.button):hover {
		border-color: rgba(255, 255, 255, 0.35) !important;
		color: rgba(255, 255, 255, 0.9) !important;
	}
</style>
