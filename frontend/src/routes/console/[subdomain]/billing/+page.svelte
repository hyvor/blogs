<script>
	import DisabledOnTemp from '../Temp/DisabledOnTemp.svelte';
	import BillingBox from './BillingBox.svelte';
	import Plans from './Plans/Plans.svelte';
	import Usage from './Usage/Usage.svelte';
</script>

<div class="view">
	<div class="column">
		<BillingBox>
			<DisabledOnTemp>
				<Plans />
			</DisabledOnTemp>
		</BillingBox>
	</div>

	<div class="column">
		<BillingBox title="Usage">
			<Usage />
		</BillingBox>
	</div>
</div>

<style>
	.view {
		height: 100%;
		display: flex;
	}

	.column {
		display: flex;
		flex: 1;
		flex-direction: column;
	}

	@media (max-width: 992px) {
		.view {
			flex-direction: column;
		}
	}
</style>
