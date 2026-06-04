<script>
	import { Button } from '@hyvor/design/components';
	import PricingPlan from './PricingPlan.svelte';

	let currency = '€';
	let yearly = $state(false);
</script>

<div class="full-wrap">
	<div class="yearly-wrap">
		<div class="yearly hds-box">
			<Button
				on:click={() => (yearly = false)}
				color="input"
				size="small"
				variant={yearly ? 'invisible' : 'fill'}>Monthly</Button
			>
			<Button
				on:click={() => (yearly = true)}
				color="input"
				size="small"
				variant={!yearly ? 'invisible' : 'fill'}
				>Yearly<span class="off">(2 months off)</span></Button
			>
		</div>
	</div>

	<div class="plans">
		<PricingPlan plan="personal" {yearly} {currency} />
		<PricingPlan plan="starter" {yearly} {currency} />
		<PricingPlan plan="growth" {yearly} {currency} />
		<PricingPlan plan="premium" {yearly} {currency} />
	</div>
</div>

<style>
	.full-wrap {
		margin: auto;
		width: 1300px;
		max-width: 100%;
		padding: 0 20px;
	}

	.plans {
		display: flex;
		gap: 15px;
		padding-top: 20px;
	}

	.yearly-wrap {
		display: flex;
		justify-content: center;
		padding-top: 35px;
	}
	.yearly {
		display: inline-flex;
		width: 350px;
		background-color: #fff;
		padding: 5px;
		border-radius: 20px;
		gap: 3px;
	}
	.yearly :global(button) {
		flex: 1;
	}
	.off {
		color: var(--text-light);
		font-size: 12px;
		margin-left: 5px;
	}

	@media (max-width: 992px) {
		.plans {
			flex-direction: column;
		}
	}
</style>
