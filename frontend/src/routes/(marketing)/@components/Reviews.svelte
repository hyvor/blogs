<script lang="ts">
	import logoAqeez from '$lib/img/customers/aqeez.svg';
	import logoSubdivisions from '$lib/img/customers/subdivisions.svg';
	import logoTimesOfMadeira from '$lib/img/customers/timesofmadeira.svg';
	import logoBeautyVouch from '$lib/img/customers/beautyvouch.png';
	import logoJobPosts from '$lib/img/customers/jobposts.webp';
	import yogakiddy from '$lib/img/customers/yogakiddy.png';
	import logoUniverbal from '$lib/img/customers/univerbal.svg';

	import { Button } from '@hyvor/design/components';
	import { IconBoxArrowUpRight } from '@hyvor/icons';

	export let button = true;

	const customers = [
		{
			name: 'Aqeez',
			logo: logoAqeez,
			url: 'https://blog.aqeez.com'
		},
		{
			name: 'Times of Madeira',
			logo: logoTimesOfMadeira,
			url: 'https://timesofmadeira.com',
			height: 65
		},
		{
			name: 'Subdivisions',
			logo: logoSubdivisions,
			url: 'https://blog.subdivisions.com'
		},
		/* {
            name: "Marko's Way",
            logo: logoMarkos,
            url: 'https://markosway.com/blog'
        }, */
		{
			name: 'Job Posts',
			logo: logoJobPosts,
			url: 'https://jobposts.ai/blog',
			width: 120,
			height: 30
		},
		{
			name: 'Yoga Kiddy',
			logo: yogakiddy,
			url: 'https://fr.yogakiddy.com',
			width: 40,
			height: 40
		},
		{
			name: 'Univerbal',
			logo: logoUniverbal,
			url: 'https://blog.univerbal.app',
			width: 130,
			height: 50
		}
	];

	const reviews = [
		{
			name: 'Lionel S.',
			role: 'Blogger',
			review:
				'I need a simple, easy-to-use, fast, beautiful and mature blogging tool that resolves the WordPress bloat. Hyvor Blogs handles this beautifully.'
		},
		{
			name: 'Manoj P.',
			role: 'Senior Application Engineer',
			review:
				' The platform offers a seamless and user-friendly experience for both bloggers and readers. The customization options are extensive, allowing bloggers to create a unique and visually appealing blog.'
		}
	];
</script>

<div class="hds-container wrap">
	<h3>Loved by 100+ bloggers & businesses</h3>

	<div class="customer-logos">
		{#each customers as customer}
			<a href={customer.url} target="_blank" rel="nofollow noopener">
				<img
					src={customer.logo}
					alt={customer.name}
					width={customer.width || 140}
					height={customer.height || 30}
				/>
			</a>
		{/each}
	</div>

	<div class="reviews">
		<svg style="display: none">
			<defs>
				<symbol id="review-star" viewBox="0 0 16 16">
					<path
						d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"
						fill="currentColor"
					/>
				</symbol>
			</defs>
		</svg>

		{#each reviews as review}
			<div class="review">
				<div class="stars">
					{#each [1, 2, 3, 4, 5] as i}
						<svg width="20" height="20">
							<use href="#review-star" />
						</svg>
					{/each}
				</div>

				<div class="review-text">
					"{review.review}"
				</div>

				<div class="user">
					- {review.name}, {review.role}
				</div>
			</div>
		{/each}
	</div>

	{#if button}
		<div class="view-on-g2">
			<Button
				as="a"
				color="gray"
				variant="fill"
				href="https://www.g2.com/products/hyvor-blogs/reviews"
				target="_blank"
			>
				View reviews on G2
				<IconBoxArrowUpRight slot="end" size={12} />
			</Button>
		</div>
	{/if}
</div>

<style lang="scss">
	.wrap {
		padding: 60px 15px;
	}
	h3 {
		text-align: center;
		font-size: 26px;
	}
	.customer-logos {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20px 0;
		flex-wrap: wrap;
		gap: 30px;
	}

	.reviews {
		margin: 10px 0;
		display: flex;
		justify-content: space-between;
		padding: 0 50px;

		.review {
			flex: 1;
			flex-shrink: 0;
			width: 400px;
			max-width: 100%;
			padding: 25px;

			.stars {
				display: flex;
				gap: 3px;
				color: #ffa500;
			}

			.review-text {
				font-size: 18px;
				margin: 20px 0;
			}

			.user {
				font-size: 16px;
				font-weight: 600;
			}
		}
	}

	.view-on-g2 {
		text-align: center;
	}

	@media (max-width: 992px) {
		.reviews {
			flex-direction: column;
			padding: 0;
			.review {
				width: 100%;
				padding: 15px;
			}
		}
	}
</style>
