<script lang="ts">
	import logoAqeez from '$lib/img/customers/aqeez.svg';
	import logoSubdivisions from '$lib/img/customers/subdivisions.svg';
	import logoTimesOfMadeira from '$lib/img/customers/timesofmadeira.svg';
	import logoJobPosts from '$lib/img/customers/jobposts.webp';
	import yogakiddy from '$lib/img/customers/yogakiddy.png';
	import logoUniverbal from '$lib/img/customers/univerbal.svg';

	const customers = [
		{ name: 'Aqeez', logo: logoAqeez },
		{ name: 'Times of Madeira', logo: logoTimesOfMadeira, height: 52 },
		{ name: 'Subdivisions', logo: logoSubdivisions },
		{ name: 'Job Posts', logo: logoJobPosts, width: 100, height: 26 },
		// { name: 'Yoga Kiddy', logo: yogakiddy, width: 36, height: 36 },
		{ name: 'Univerbal', logo: logoUniverbal, width: 110, height: 42 }
	];
</script>

<section class="logo-strip">
	<div class="hds-container inner">
		<p class="label">Trusted by 100+ bloggers &amp; businesses</p>
		<div class="logos">
			{#each customers as c}
				<img
					src={c.logo}
					alt={c.name}
					width={c.width ?? 120}
					height={c.height ?? 28}
					class="logo"
				/>
			{/each}
		</div>
	</div>
</section>

<style>
	.logo-strip {
		/* background: var(--accent); */
		background: #574443;
		padding: 52px 0;
	}

	.inner {
		text-align: center;
	}

	.label {
		font-size: 16px;
		font-weight: 600;
		letter-spacing: 0.07em;
		text-transform: uppercase;
		color: var(--accent-light-mid);
		margin: 0 0 36px;
	}

	.logos {
		display: flex;
		align-items: center;
		justify-content: center;
		flex-wrap: wrap;
		gap: 24px 52px;
	}

	.logo {
		filter: brightness(0) invert(1);
		opacity: 0.8;
		display: block;
	}
</style>
