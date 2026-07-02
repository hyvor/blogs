<script lang="ts">
	import { Button } from '@hyvor/design/components';
	import IconBoxArrowUpRight from '@hyvor/icons/IconBoxArrowUpRight';

	const testimonials = [
		{
			quote: 'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.',
			name: 'Lionel S.',
			role: 'Blogger',
		},
		{
			quote: 'The platform offers a seamless and user-friendly experience for both bloggers and readers. The customisation options are extensive, allowing us to create a unique and visually appealing blog.',
			name: 'Manoj P.',
			role: 'Senior Application Engineer',
		},
	];
</script>

<section class="testimonials">
	<div class="hds-container inner">
		<p class="label">What our customers say</p>

		<div class="cards">
			{#each testimonials as t}
				<figure class="card">
					<div class="stars" aria-label="5 out of 5 stars">
						{#each [1,2,3,4,5] as _}
							<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" fill="#f59e0b">
								<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
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

<style lang="scss">
	.testimonials {
		background: #0f0f11;
		padding: 96px 0;
	}

	.inner {
		text-align: center;
	}

	.label {
		font-size: 13px;
		font-weight: 600;
		letter-spacing: 0.07em;
		text-transform: uppercase;
		color: rgba(255, 255, 255, 0.35);
		margin: 0 0 48px;
	}

	.cards {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
		text-align: left;
		margin-bottom: 40px;
	}

	.card {
		border-radius: 20px;
		border: 1px solid rgba(255, 255, 255, 0.08);
		background: rgba(255, 255, 255, 0.04);
		padding: 32px;
		margin: 0;
		display: flex;
		flex-direction: column;
		gap: 20px;
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

		strong {
			font-size: 14px;
			font-weight: 600;
			color: #fff;
		}

		span {
			font-size: 13px;
			color: rgba(255, 255, 255, 0.4);
		}
	}

	.g2-link {
		:global(.button) {
			border-color: rgba(255, 255, 255, 0.15) !important;
			color: rgba(255, 255, 255, 0.6) !important;
			background: transparent !important;

			&:hover {
				border-color: rgba(255, 255, 255, 0.35) !important;
				color: rgba(255, 255, 255, 0.9) !important;
			}
		}
	}

	@media (max-width: 768px) {
		.cards {
			grid-template-columns: 1fr;
		}
	}
</style>