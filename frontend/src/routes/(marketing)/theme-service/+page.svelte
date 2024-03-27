<script lang="ts">
	import { Switch, SplitControl, Caption, Label } from '@hyvor/design/components';
	import FeatureSectionTitle from '../@homepage/FeatureSectionTitle.svelte';
	import ThemeService from './ThemeService.svelte';
	//import { Switch } from "@hyvor/design/components";
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

<!-- <div class="price hds-container">
	<div class="pricing">
	<div class="price-head">1000€/Theme</div>
	</div>
	<div class="situations">
		<h3>Our Offers</h3>
		<ul>
            <li>
                <div class="item">
                    <div class="switch"><Switch>Provide Your Design</Switch></div>
                    <div class="description">
                        You get a 20% discount
                    </div>
                </div>
            </li>
           
            <li>
                <div class="item">
                    <div class="switch"><Switch /> 
                        <div class="paragraphs">Publish in Our Theme Catalogue</div>
                    </div>
                    <div class="description">
                        <div class="paragraphs">You get a 50% discount</div>
                    </div>
                </div>
            </li>

            <li>
                <div class="item">
                    <div class="switch"><Switch /> <div class="paragraphs">You are a Non-Profit Organization</div></div>
                    <div class="description">
                        <div class="paragraphs">You get a 20% discount</div>
                    </div>
                </div>
                
        </ul>
		<SplitControl>
			<Switch />
			<Label slot="label">Spam Detection</Label>
			<Caption slot="caption">Check for spam in comments</Caption>
		</SplitControl>
	</div>
</div> -->
<div class="price hds-container">
	<div class="main-price">1000€/Theme</div>

	<div class="instances">
		<SplitControl>
			<Switch />
			<Label slot="label">Provide Your Design</Label>
			<Caption slot="caption">You get a 20% discount</Caption>
		</SplitControl>

		<SplitControl>
			<Switch />
			<Label slot="label">Publish in Our Theme Catalogue</Label>
			<Caption slot="caption">You get a 50% discount</Caption>
		</SplitControl>

		<SplitControl>
			<Switch />
			<Label slot="label">You are a Non-Profit Organization</Label>
			<Caption slot="caption">You get a 20% discount</Caption>
		</SplitControl>
	</div>
</div>

<FeatureSectionTitle
	title="Theme Customization Service"
	subtitle="By the Hyvor Blogs Team"
	wrapStyle="margin-top:60px"
/>

<style lang="scss">
	// .price {
	// 	display: flex;
	// 	justify-content: center;
	// 	align-items: center;
	// 	margin-top: 60px;
	// 	padding: 60px 20px;
	// 	border-radius: 20px;

	// 	background-color: white;
	// 	box-shadow: var(--box-shadow);

	// 	.pricing {
	// 		display: flex;
	// 		justify-content: center;
	// 		align-items: center;
	// 		width: 40%;
	// 		padding: 20px;
	// 		margin-inline-end: 30px;

	// 		border-inline-end: 1px solid var(--blue-light);
	// 	}

	// 	.price-head {
	// 		font-size: 2.5rem;
	// 		color: var(--text);
	// 		font-weight: 700;
	// 	}

	// 	// .situations {
	// 	// 	display: flex;
	// 	// 	flex-direction: row;
	// 	// 	width: 60%;

	// 	// 	ul {
	// 	// 		list-style: none;
	// 	// 		padding: 0;
	// 	// 		margin: 0;
	// 	// 		display: flex;
	// 	// 		flex-direction: column;
	// 	// 		justify-content: center;
	// 	// 		align-items: center;
	// 	// 		width: 100%;

	// 	// 		li {
	// 	// 			display: flex;
	// 	// 			width: 100%;
	// 	// 			border-radius: 20px;
	// 	// 			color: rgb(0, 0, 0);
	// 	// 			font-size: 1.5rem;
	// 	// 			font-weight: 500;
	// 	// 			transition: all 0.3s ease;
	// 	// 			cursor: pointer;
	// 	// 			margin: 30px 0;

	// 	// 			&:hover {
	// 	// 				// background-color: var(--blue-dark);
	// 	// 			}

	// 	// 			.item {
	// 	// 				display: flex;
	// 	// 				justify-content: center;
	// 	// 				align-items: center;

	// 	// 				flex-direction: column;
	// 	// 				.description {
	// 	// 					font-size: 1rem;
	// 	// 					color: var(--text-light);
	// 	// 				}

	// 	// 				.switch {
	// 	// 					display: flex;
	// 	// 					justify-content: center;
	// 	// 					align-items: center;
	// 	// 					flex-direction: row;
	// 	// 					font-size: 1rem;
	// 	// 					.paragraphs {
	// 	// 						font-size: 1.3rem;
	// 	// 						color: var(--text);
	// 	// 						margin-inline-start: 20px;
	// 	// 						display: flex;
	// 	// 						align-items: center;
	// 	// 					}
	// 	// 				}
	// 	// 			}
	// 	// 		}
	// 	// 	}
	// 	// }
	// }

	// @media (max-width: 768px) {
	// 	.price {
	// 		flex-direction: column;
	// 		padding: 20px;
	// 		.pricing {
	// 			width: 100%;
	// 			margin-inline-end: 0;
	// 			border-inline-end: none;
	// 			padding: 0;
	// 			margin-bottom: 20px;
	// 		}

	// 		// .situations {
	// 		// 	//center align

	// 		// 	width: 100%;
	// 		// 	ul {
	// 		// 		li {
	// 		// 			margin: 20px 0;
	// 		// 		}
	// 		// 	}
	// 		// }
	// 	}
	// }

	.price {
		display: flex;
		flex-direction: row;
		background: white;
		border-radius: 20px;
		padding: 40px 0;

		.main-price {
			font-size: 2.5rem;
			color: var(--text);
			font-weight: 700;
			margin-bottom: 20px;
			width: 50%;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.instances {
			width: 50%;
			display: flex;
			flex-direction: column;
			align-items: center;
		}
	}
</style>
