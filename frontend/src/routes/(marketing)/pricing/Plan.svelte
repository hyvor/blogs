<script lang="ts">
	interface Props {
		name: string;
		price: number;
		annualOnly?: boolean;
	}

	let { name, price, annualOnly = false }: Props = $props();
</script>

<div class="plan hds-box">
	<div class="inner">
		<div class="name">{name}</div>
		<div class="price-wrap">
			<span class="price">€{price}</span><span class="freq">/{annualOnly ? 'year' : 'month'}</span>
		</div>
		{#if annualOnly}
			<div class="annual-note">billed annually</div>
		{/if}
	</div>
</div>

<style>
	.plan {
		flex: 1;
		min-width: 0;
		margin-left: 15px;
	}
	.inner {
		padding: 15px 25px;
	}

	.name {
		font-weight: 600;
		font-size: 18px;
	}

	.price-wrap {
		margin-top: 4px;
	}

	.price {
		font-weight: 600;
		font-size: 28px;
	}

	.freq {
		color: var(--text-light);
	}

	.annual-note {
		font-size: 0.8rem;
		color: var(--text-light);
		margin-top: 2px;
	}

	@media (max-width: 1000px) {
		.plan {
			flex: 3;
		}
	}
</style>
