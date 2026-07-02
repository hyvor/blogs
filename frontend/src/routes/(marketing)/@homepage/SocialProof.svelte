<script lang="ts">
	import logoAqeez from '$lib/img/customers/aqeez.svg';
	import logoSubdivisions from '$lib/img/customers/subdivisions.svg';
	import logoTimesOfMadeira from '$lib/img/customers/timesofmadeira.svg';
	import logoBeautyVouch from '$lib/img/customers/beautyvouch.png';
	import logoJobPosts from '$lib/img/customers/jobposts.webp';
	import yogakiddy from '$lib/img/customers/yogakiddy.png';
	import logoUniverbal from '$lib/img/customers/univerbal.svg';

	const customers = [
		{ name: 'Aqeez', logo: logoAqeez, url: 'https://blog.aqeez.com' },
		{ name: 'Times of Madeira', logo: logoTimesOfMadeira, url: 'https://timesofmadeira.com', height: 65 },
		{ name: 'Subdivisions', logo: logoSubdivisions, url: 'https://blog.subdivisions.com' },
		{ name: 'Job Posts', logo: logoJobPosts, url: 'https://jobposts.ai/blog', width: 120, height: 30 },
		{ name: 'Yoga Kiddy', logo: yogakiddy, url: 'https://fr.yogakiddy.com', width: 40, height: 40 },
		{ name: 'Univerbal', logo: logoUniverbal, url: 'https://blog.univerbal.app', width: 130, height: 50 }
	];

	const reviews = [
		{
			name: 'Lionel S.',
			role: 'Blogger',
			review: 'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.'
		},
		{
			name: 'Manoj P.',
			role: 'Senior Application Engineer',
			review: 'The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog.'
		}
	];
</script>

<section class="social-proof hds-container">
	<p class="label">Trusted by 100+ bloggers &amp; businesses worldwide</p>

	<div class="logos">
		{#each customers as customer}
			<a href={customer.url} target="_blank" rel="nofollow noopener" class="logo-link">
				<img
					src={customer.logo}
					alt={customer.name}
					width={customer.width || 140}
					height={customer.height || 30}
				/>
			</a>
		{/each}
	</div>

	<div class="divider"></div>

	<div class="reviews">
		{#each reviews as review}
			<div class="review">
				<div class="stars" aria-label="5 stars">
					{#each [1,2,3,4,5] as _}
						<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
							<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" fill="currentColor"/>
						</svg>
					{/each}
				</div>
				<blockquote class="quote">"{review.review}"</blockquote>
				<cite class="attribution">{review.name}, {review.role}</cite>
			</div>
		{/each}
	</div>
</section>

<style lang="scss">
	.social-proof {
		padding: 64px 0;
		text-align: center;
	}

	.label {
		margin: 0 0 32px;
		font-size: 14px;
		font-weight: 600;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		color: var(--text-light);
	}

	.logos {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-wrap: wrap;
		gap: 32px 48px;
	}

	.logo-link {
		opacity: 0.55;
		transition: opacity 0.15s;
		display: flex;
		align-items: center;
		&:hover {
			opacity: 1;
		}
		img {
			display: block;
		}
	}

	.divider {
		width: 60px;
		height: 1px;
		background: var(--border);
		margin: 48px auto;
	}

	.reviews {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 24px;
	}

	.review {
		text-align: left;
		padding: 28px 32px;
		border-radius: 20px;
		border: 1px solid var(--border);
		background: var(--background-2, var(--box-background));
	}

	.stars {
		display: flex;
		gap: 3px;
		color: #f59e0b;
		margin-bottom: 16px;
	}

	.quote {
		font-size: 16px;
		line-height: 1.6;
		color: var(--text);
		margin: 0 0 16px;
		font-style: normal;
	}

	.attribution {
		font-size: 14px;
		font-weight: 600;
		color: var(--text-light);
		font-style: normal;
	}

	@media (max-width: 768px) {
		.reviews {
			grid-template-columns: 1fr;
		}
		.logos {
			gap: 24px 32px;
		}
	}
</style>
