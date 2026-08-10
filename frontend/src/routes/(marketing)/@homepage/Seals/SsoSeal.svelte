<script lang="ts">
	import IconShieldLockFill from '@hyvor/icons/IconShieldLockFill';
	import SealBase from './SealBase.svelte';
</script>

<SealBase>
	<span class="icon">
		<IconShieldLockFill size={22} />
	</span>
	<span class="label">SSO</span>
</SealBase>

<style>
	.icon {
		display: flex;
		color: #e3b34a;
	}

	.label {
		font-size: 13px;
		font-weight: 700;
		letter-spacing: 0.03em;
		color: #fff;
	}
</style>
