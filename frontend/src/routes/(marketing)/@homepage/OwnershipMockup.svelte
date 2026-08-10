<script lang="ts">
	import IconNewspaper from '@hyvor/icons/IconNewspaper';
	import IconKeyFill from '@hyvor/icons/IconKeyFill';
	import IconCloud from '@hyvor/icons/IconCloud';
	import IconServer from '@hyvor/icons/IconServer';
	import IconArrowLeftRight from '@hyvor/icons/IconArrowLeftRight';
	import IconShieldCheck from '@hyvor/icons/IconShieldCheck';
	import IconUnlock from '@hyvor/icons/IconUnlock';
</script>

<div class="ownership-mockup">
	<!-- your content, your key -->
	<div class="content-hero">
		<div class="content-icon">
			<IconNewspaper size={38} />
		</div>
		<div class="key-badge">
			<IconKeyFill size={22} />
		</div>
	</div>

	<!-- portable between our cloud and your own server -->
	<div class="portability-row">
		<div class="port-icon"><IconCloud size={30} /></div>
		<div class="port-swap"><IconArrowLeftRight size={18} /></div>
		<div class="port-icon"><IconServer size={30} /></div>
	</div>

	<!-- private and unlocked -->
	<div class="badges-row">
		<span class="icon-badge"><IconShieldCheck size={24} /></span>
		<span class="icon-badge"><IconUnlock size={24} /></span>
	</div>
</div>

<style>
	.ownership-mockup {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 26px;
		padding: 44px 20px;
	}

	.content-hero {
		position: relative;
	}

	.content-icon,
	.port-icon,
	.icon-badge {
		color: var(--accent);
		background: color-mix(in srgb, var(--accent) 20%, var(--background));
	}

	.key-badge {
		background: var(--accent);
		color: var(--background);
	}

	.content-icon {
		width: 104px;
		height: 104px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.key-badge {
		position: absolute;
		right: -8px;
		bottom: -8px;
		width: 48px;
		height: 48px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		transform: rotate(-10deg);
	}

	.portability-row {
		display: flex;
		align-items: center;
		gap: 16px;
	}

	.port-icon {
		width: 64px;
		height: 64px;
		border-radius: 16px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.port-swap {
		display: flex;
		align-items: center;
		color: var(--text-light);
		opacity: 0.6;
	}

	.badges-row {
		display: flex;
		gap: 12px;
	}

	.icon-badge {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 48px;
		height: 48px;
		border-radius: 100px;
	}
</style>
