<script lang="ts">
	import IconAwardFill from '@hyvor/icons/IconAwardFill';
	import SealBase from './SealBase.svelte';
</script>

<SealBase pending>
	<span class="icon">
		<IconAwardFill size={20} />
	</span>
	<span class="label">ISO 27001</span>
	<span class="note">Soon</span>
</SealBase>

<style>
	.icon {
		display: flex;
		color: rgba(255, 255, 255, 0.45);
	}

	.label {
		font-size: 11px;
		font-weight: 700;
		letter-spacing: 0.02em;
		color: rgba(255, 255, 255, 0.55);
	}

	.note {
		position: absolute;
		bottom: -10px;
		font-size: 10px;
		font-weight: 600;
		padding: 2px 8px;
		border-radius: 100px;
		color: rgba(255, 255, 255, 0.6);
		background: rgba(255, 255, 255, 0.1);
		white-space: nowrap;
	}
</style>
