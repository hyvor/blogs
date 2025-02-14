<script lang="ts">
	import { Switch, Button, Caption } from '@hyvor/design/components';
	import FeatureSectionTitle from '../@homepage/FeatureSectionTitle.svelte';
	import ThemeService from './ThemeService.svelte';

	let Oprice = 1000;
	let price = $state(Oprice);
	let d20 = 0.2;
	let d30 = 0.3;

	// variables for switches
	let switch1 = $state(false);
	let switch2 = $state(false);

	// function to update price
	function updatePrice() {
		price = 1000;
		if (switch1) {
			price = Oprice - Oprice * d20;
		}
		if (switch2) {
			price = Oprice - Oprice * d30;
		}

		if (switch1 && switch2) {
			price = Oprice - Oprice * (d20 + d30);
		}
	}
</script>

<svelte:head>
	<title>Theme Service - Hyvor Blogs</title>
	<link rel="canonical" href="https://blogs.hyvor.com/theme-service" />
</svelte:head>

<FeatureSectionTitle
	title="Theme Developement Service"
	subtitle="By the Hyvor Blogs Team"
	wrapStyle="margin-top:60px"
/>

<ThemeService />

<div class="price hds-container">
	<div class="title">Pricing</div>
	<div class="subtitle">Design & Developement Included!</div>

	<div class="main-price">${price}&nbsp;<Caption>per theme</Caption></div>

	<div class="instances">
		<div class="mid-box">
			<div class="switch">
				<Switch bind:checked={switch1} on:change={updatePrice}
					>You Provide the design (ex: Figma) <Caption>20% discount</Caption></Switch
				>
			</div>

			<div class="switch">
				<Switch bind:checked={switch2} on:change={updatePrice}
					>Publish your theme in our theme directory <Caption>30% discount</Caption
					></Switch
				>
			</div>
		</div>
	</div>
</div>

<div class="buttons">
	<div class="cta">
		<Button
			as="a"
			href="https://hyvor.com/support"
			target="_blank"
			variant="fill"
			size="large"
			color="gray"
		>
			Request a Theme Now &rarr;
		</Button>
	</div>

	<div class="or">OR</div>

	<div class="cta">
		<Button
			as="a"
			href="mailto:blogs.support@hyvor.com"
			variant="outline"
			size="large"
			color="gray">Email Us</Button
		>
	</div>
</div>

<style lang="scss">
	.title {
		font-size: 38px;
		font-weight: 700;
		margin-bottom: 10px;
		margin-top: 30px;
		text-align: center;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.subtitle {
		font-size: 20px;
		width: 650px;
		max-width: 100%;
		margin: 0 auto;
		font-weight: normal;
		line-height: 1.4;
		text-align: center;
		margin-bottom: 40px;
		color: var(--text-light);
	}

	.price {
		display: flex;
		flex-direction: column;
		background: white;
		border-radius: 20px;
		padding: 0 0;
		width: 700px;

		.main-price {
			font-size: 2rem;
			color: var(--text);
			font-weight: 600;

			width: 100%;
			display: flex;
			justify-content: center;
			align-items: center;
			background-color: var(--green-light);
			padding: 20px;
		}

		.instances {
			width: 100%;
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 20px 0;

			.mid-box {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;

				margin: 0 auto;

				div {
					align-self: self-start;
					padding: 20px 0;
				}
			}
		}
	}

	.buttons {
		display: flex;
		justify-content: center;
		margin-top: 30px;
		flex-direction: column;

		.cta {
			display: flex;
			justify-content: center;
			margin-bottom: 30px;
			margin-top: 30px;
		}

		.or {
			font-size: 20px;
			font-weight: 600;
			text-align: center;
			color: var(--text-light);
		}
	}
</style>
