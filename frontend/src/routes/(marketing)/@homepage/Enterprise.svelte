<script lang="ts">
	import { SpotlightSplit } from '@hyvor/design/marketing';
	import GdprSeal from './Seals/GdprSeal.svelte';
	import CcpaSeal from './Seals/CcpaSeal.svelte';
	import SsoSeal from './Seals/SsoSeal.svelte';
	import IsoSeal from './Seals/IsoSeal.svelte';
</script>

<SpotlightSplit
	title="Enterprise-ready."
	description="Enterprise-grade security and compliance, without adding complexity to your publishing workflow."
	stats={['> 99.9% Uptime', 'Priority Support']}
	button={{ href: 'https://hyvor.com/enterprise', label: 'Contact Sales', external: true }}
	background="#574443"
>
	{#snippet content()}
		<div class="seals">
			<GdprSeal />
			<CcpaSeal />
			<SsoSeal />
			<IsoSeal />
		</div>
	{/snippet}
</SpotlightSplit>

<style>
	.seals {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 20px;
	}

	@media (max-width: 768px) {
		.seals {
			grid-template-columns: repeat(4, 1fr);
		}
	}

	@media (max-width: 480px) {
		.seals {
			grid-template-columns: repeat(2, 1fr);
		}
	}
</style>
