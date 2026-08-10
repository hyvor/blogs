<script lang="ts">
	import IconChatDotsFill from '@hyvor/icons/IconChatDotsFill';
	import IconEnvelopeFill from '@hyvor/icons/IconEnvelopeFill';
	import IconSearch from '@hyvor/icons/IconSearch';
	import IconPaletteFill from '@hyvor/icons/IconPaletteFill';
	import IconGlobe2 from '@hyvor/icons/IconGlobe2';
	import IconShieldCheck from '@hyvor/icons/IconShieldCheck';
	import IconPuzzleFill from '@hyvor/icons/IconPuzzleFill';

	function toXY(angleDeg: number, radius: number) {
		const rad = (angleDeg * Math.PI) / 180;
		return { x: +(Math.cos(rad) * radius).toFixed(1), y: +(Math.sin(rad) * radius).toFixed(1) };
	}

	const featureIcons = [
		IconChatDotsFill,
		IconEnvelopeFill,
		IconSearch,
		IconPaletteFill,
		IconGlobe2,
		IconShieldCheck
	];

	// starting formation: 7 crowded slots (6 features + the plugin, at the top)
	const step7 = 360 / (featureIcons.length + 1);
	const pluginStart = toXY(-90, 100);

	// final formation: the 6 features settle into a roomier, evenly-spaced ring
	const step6 = 360 / featureIcons.length;

	const features = featureIcons.map((icon, i) => ({
		icon,
		start: toXY(-90 + step7 * (i + 1), 100),
		end: toXY(-90 + step6 * i, 114),
		delay: i * 0.06
	}));
</script>

<div class="all-in-one-mockup">
	<div class="orbit-wrap">
		<div class="package-hero">
			<img src="/logo.svg" alt="" width="60" height="60" />
		</div>

		{#each features as f}
			<div
				class="orbit-icon"
				style="--x0:{f.start.x}px; --y0:{f.start.y}px; --x1:{f.end.x}px; --y1:{f.end.y}px; animation-delay:{f.delay}s"
			>
				<f.icon size={24} />
			</div>
		{/each}

		<!-- the plugin doesn't get to stay -->
		<div
			class="rejected-plugin"
			style="--x0:{pluginStart.x}px; --y0:{pluginStart.y}px"
		>
			<IconPuzzleFill size={22} />
		</div>
	</div>
</div>

<style>
	.all-in-one-mockup {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 30px;
		padding: 40px 20px 24px;
	}

	.orbit-wrap {
		position: relative;
		width: 280px;
		height: 280px;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.package-hero {
		position: relative;
		width: 116px;
		height: 116px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: color-mix(in srgb, var(--accent) 14%, transparent);
	}

	.orbit-icon {
		position: absolute;
		width: 52px;
		height: 52px;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		color: color-mix(in srgb, var(--text) 75%, transparent);
		background: color-mix(in srgb, var(--text) 10%, var(--background));
		box-shadow: 0 4px 12px color-mix(in srgb, var(--text) 8%, transparent);
		transform: translate(var(--x0), var(--y0));
		animation: settle-in 4.5s cubic-bezier(0.22, 1, 0.36, 1) infinite;
	}

	@keyframes settle-in {
		0%,
		10% {
			transform: translate(var(--x0), var(--y0)) scale(0.94);
			opacity: 0.85;
		}
		60%,
		100% {
			transform: translate(var(--x1), var(--y1)) scale(1);
			opacity: 1;
		}
	}

	.rejected-plugin {
		position: absolute;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 44px;
		height: 44px;
		border-radius: 100px;
		color: var(--red-dark, var(--red));
		background: color-mix(in srgb, var(--red) 18%, var(--background));
		box-shadow: 0 4px 12px color-mix(in srgb, var(--text) 8%, transparent);
		transform: translate(var(--x0), var(--y0));
		animation: plugin-kick 4.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
	}

	@keyframes plugin-kick {
		0%,
		12% {
			transform: translate(var(--x0), var(--y0)) rotate(0deg) scale(1);
			opacity: 1;
		}
		22% {
			transform: translate(calc(var(--x0) * 1.2), calc(var(--y0) * 1.2)) rotate(-18deg) scale(1.08);
			opacity: 1;
		}
		30% {
			transform: translate(calc(var(--x0) * 0.85), calc(var(--y0) * 0.85)) rotate(12deg)
				scale(0.92);
			opacity: 1;
		}
		55% {
			transform: translate(170px, -200px) rotate(-160deg) scale(0.5);
			opacity: 1;
		}
		68%,
		100% {
			transform: translate(170px, -200px) rotate(-160deg) scale(0.5);
			opacity: 0;
		}
	}

	@media (max-width: 480px) {
		.orbit-wrap {
			width: 220px;
			height: 220px;
			transform: scale(0.85);
		}
	}
</style>
