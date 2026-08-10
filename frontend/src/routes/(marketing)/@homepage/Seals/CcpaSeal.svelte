<script lang="ts">
	import SealBase from './SealBase.svelte';
	import ccpa from './ccpa.svg?url';
</script>

<SealBase>
	<img src={ccpa} alt="CCPA Seal" class="map" />
	<span class="label">CCPA</span>
</SealBase>

<style>
	.map {
		width: 22px;
		height: auto;
	}

	.label {
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.03em;
		color: #fff;
	}
</style>
